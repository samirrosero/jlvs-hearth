<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HorarioMedico;
use App\Models\HistoriaClinica;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => ['required', 'string', 'max:500'],
        ]);

        $rol     = auth()->user()->rol?->nombre ?? 'administrador';
        $mensaje = $request->input('mensaje');
        $datos   = $this->recopilarDatos($rol, $mensaje);
        $prompt  = $this->buildPrompt($rol, $datos);

        $respuesta = Http::timeout(30)
            ->withHeaders(['Authorization' => 'Bearer ' . env('GROQ_API_KEY')])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'    => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user',   'content' => $request->input('mensaje')],
                ],
                'max_tokens' => 1024,
            ]);

        if ($respuesta->failed()) {
            return response()->json([
                'error' => 'No se pudo conectar con el asistente.',
            ], 503);
        }

        $texto = $respuesta->json('choices.0.message.content') ?? 'Sin respuesta del modelo.';

        return response()->json(['respuesta' => $texto]);
    }

    // ─────────────────────────────────────────────────────────────
    // Recopilación de datos por rol
    // ─────────────────────────────────────────────────────────────

    private function recopilarDatos(string $rol, string $mensaje = ''): array
    {
        $hoy = now()->toDateString();

        return match ($rol) {
            'medico'       => $this->datosRolMedico($hoy),
            'gestor_citas' => $this->datosRolGestor($hoy, $mensaje),
            'paciente'     => $this->datosRolPaciente($hoy),
            default        => $this->datosRolAdmin($hoy, $mensaje),
        };
    }

    private function datosRolAdmin(string $hoy, string $mensaje = ''): array
    {
        $empresaId = auth()->user()->empresa_id;
        $datos = [
            'fecha_hoy'    => now()->translatedFormat('l j \d\e F \d\e Y'),
            'medico_info'  => null,
        ];

        foreach (config('chatbot.datos') as $item) {
            $query = $item['model']::where('empresa_id', $empresaId);
            if (!empty($item['where'])) {
                $query->where($item['where']);
            }
            $datos[$item['clave']] = $query->count();
        }

        $datos['citas_hoy']        = Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count();
        $datos['citas_pendientes'] = Cita::where('empresa_id', $empresaId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();

        // Si el mensaje menciona un nombre de médico, buscar sus datos reales
        if (preg_match('/(?:doctor(?:a)?|dr\.?|dra\.?|médico|medico)\s+([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)/iu', $mensaje, $m)
            || preg_match('/horarios?\s+(?:de(?:l?)?\s+)?([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)/iu', $mensaje, $m)) {
            $nombre  = trim($m[1]);
            $medico  = Medico::where('empresa_id', $empresaId)
                ->whereHas('usuario', fn ($q) => $q->where('nombre', 'like', "%{$nombre}%"))
                ->with(['usuario', 'horarios' => fn ($q) => $q->where('activo', true)->orderBy('dia_semana')->orderBy('hora_inicio')])
                ->first();

            if ($medico) {
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $horarioTexto = $medico->horarios->isEmpty()
                    ? '  Sin horarios registrados'
                    : $medico->horarios->map(fn ($h) =>
                        sprintf('  - %s: %s – %s', $dias[$h->dia_semana], substr($h->hora_inicio, 0, 5), substr($h->hora_fin, 0, 5))
                    )->join("\n");

                $citasHoy = Cita::where('medico_id', $medico->id)->where('fecha', $hoy)->count();

                $datos['medico_info'] = [
                    'encontrado'   => true,
                    'nombre'       => $medico->usuario->nombre,
                    'especialidad' => $medico->especialidad ?? '—',
                    'registro'     => $medico->registro_medico ?? '—',
                    'citas_hoy'    => $citasHoy,
                    'horarios'     => $horarioTexto,
                ];
            } else {
                $datos['medico_info'] = ['encontrado' => false, 'busqueda' => $nombre];
            }
        }

        return $datos;
    }

    private function datosRolMedico(string $hoy): array
    {
        $medico = auth()->user()->medico;
        if (!$medico) {
            return ['fecha_hoy' => now()->translatedFormat('l j \d\e F \d\e Y')];
        }

        return [
            'fecha_hoy'         => now()->translatedFormat('l j \d\e F \d\e Y'),
            'citas_hoy'         => Cita::where('medico_id', $medico->id)->where('fecha', $hoy)->count(),
            'citas_mes'         => Cita::where('medico_id', $medico->id)
                                        ->whereMonth('fecha', now()->month)
                                        ->whereYear('fecha', now()->year)
                                        ->count(),
            'citas_pendientes'  => Cita::where('medico_id', $medico->id)
                                        ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
                                        ->count(),
            'total_pacientes'   => Cita::where('medico_id', $medico->id)
                                        ->distinct('paciente_id')
                                        ->count('paciente_id'),
            'total_historias'   => HistoriaClinica::whereHas(
                                        'ejecucionCita.cita',
                                        fn ($q) => $q->where('medico_id', $medico->id)
                                    )->count(),
        ];
    }

    private function datosRolGestor(string $hoy, string $mensaje = ''): array
    {
        $empresaId = auth()->user()->empresa_id;

        $datos = [
            'fecha_hoy'        => now()->translatedFormat('l j \d\e F \d\e Y'),
            'citas_hoy'        => Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count(),
            'citas_pendientes' => Cita::where('empresa_id', $empresaId)
                                       ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
                                       ->count(),
            'total_pacientes'  => \App\Models\Paciente::where('empresa_id', $empresaId)->count(),
            'total_medicos'    => \App\Models\Medico::where('empresa_id', $empresaId)->count(),
            'en_espera'        => \App\Models\ListaEspera::where('empresa_id', $empresaId)
                                       ->where('estado', 'esperando')
                                       ->count(),
            'paciente_info'    => null,
        ];

        // Si el mensaje contiene una cédula (5-12 dígitos), buscar el paciente real
        if (preg_match('/\b(\d{5,12})\b/', $mensaje, $m)) {
            $cedula   = $m[1];
            $paciente = \App\Models\Paciente::where('empresa_id', $empresaId)
                ->where('identificacion', $cedula)
                ->first();

            if ($paciente) {
                $ultimasCitas = Cita::where('paciente_id', $paciente->id)
                    ->with('estado', 'servicio', 'medico.usuario')
                    ->orderByDesc('fecha')->orderByDesc('hora')
                    ->limit(5)
                    ->get()
                    ->map(fn ($c) => sprintf(
                        '  - %s %s | %s | %s | Dr/a. %s',
                        $c->fecha,
                        substr($c->hora ?? '', 0, 5),
                        $c->servicio?->nombre ?? 'Sin servicio',
                        $c->estado?->nombre ?? '—',
                        $c->medico?->usuario->nombre ?? $c->medico?->usuario->name ?? '—'
                    ))
                    ->join("\n");

                $enEspera = \App\Models\ListaEspera::where('paciente_id', $paciente->id)
                    ->where('estado', 'esperando')->exists();

                $datos['paciente_info'] = [
                    'encontrado'      => true,
                    'cedula'          => $paciente->identificacion,
                    'nombre'          => $paciente->nombre_completo,
                    'telefono'        => $paciente->telefono ?? '—',
                    'correo'          => $paciente->correo   ?? '—',
                    'fecha_nacimiento'=> $paciente->fecha_nacimiento ?? '—',
                    'sexo'            => $paciente->sexo     ?? '—',
                    'ultimas_citas'   => $ultimasCitas ?: '  Sin citas registradas',
                    'en_espera'       => $enEspera ? 'Sí, tiene registros en lista de espera' : 'No',
                ];
            } else {
                $datos['paciente_info'] = ['encontrado' => false, 'cedula' => $cedula];
            }
        }

        return $datos;
    }

    private function datosRolPaciente(string $hoy): array
    {
        $paciente = auth()->user()->paciente;
        if (!$paciente) {
            return ['fecha_hoy' => now()->translatedFormat('l j \d\e F \d\e Y')];
        }

        return [
            'fecha_hoy'          => now()->translatedFormat('l j \d\e F \d\e Y'),
            'proximas_citas'     => Cita::where('paciente_id', $paciente->id)
                                         ->where('fecha', '>=', $hoy)
                                         ->count(),
            'total_citas'        => Cita::where('paciente_id', $paciente->id)->count(),
            'total_historias'    => HistoriaClinica::where('paciente_id', $paciente->id)->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Prompts por rol
    // ─────────────────────────────────────────────────────────────

    private function buildPrompt(string $rol, array $d): string
    {
        return match ($rol) {
            'medico'       => $this->promptMedico($d),
            'gestor_citas' => $this->promptGestor($d),
            'paciente'     => $this->promptPaciente($d),
            default        => $this->promptAdmin($d),
        };
    }

    private function promptAdmin(array $d): string
    {
        $secciones     = collect(config('chatbot.secciones'));
        $clavesValidas = $secciones->pluck('clave')->join(', ');

        $listaSecciones = $secciones
            ->map(fn ($s) => "- {$s['label']}: {$s['descripcion']}")
            ->join("\n");

        $listaDatos = collect(config('chatbot.datos'))
            ->map(fn ($item) => "- {$item['label']}: {$d[$item['clave']]}")
            ->join("\n");

        // Sección de médico consultado (si aplica)
        $seccionMedico = '';
        if ($d['medico_info']) {
            $m = $d['medico_info'];
            if (! $m['encontrado']) {
                $seccionMedico = "\n=== BÚSQUEDA DE MÉDICO ===\nNo se encontró ningún médico con el nombre \"{$m['busqueda']}\" en esta IPS.\n";
            } else {
                $seccionMedico = "\n=== MÉDICO ENCONTRADO (DATOS REALES DE LA BD) ===\n"
                    . "Nombre:       {$m['nombre']}\n"
                    . "Especialidad: {$m['especialidad']}\n"
                    . "Registro:     {$m['registro']}\n"
                    . "Citas hoy:    {$m['citas_hoy']}\n"
                    . "Horarios laborales activos:\n{$m['horarios']}\n"
                    . "INSTRUCCIÓN OBLIGATORIA: Responde DIRECTAMENTE con estos datos. NO navegues, NO uses [NAVEGAR:].\n";
            }
        }

        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth, software médico para IPS colombianas.
Ayudas al administrador a entender el estado del sistema y a navegar por él.

=== DATOS ACTUALES DE LA IPS ===
{$listaDatos}
- Citas programadas para hoy ({$d['fecha_hoy']}): {$d['citas_hoy']}
- Citas pendientes: {$d['citas_pendientes']}
{$seccionMedico}
=== SECCIONES DE LA PLATAFORMA ===
{$listaSecciones}

=== MARCADORES ===
Tienes tres tipos de marcadores. Úsalos al final de la oración relevante:

1. [NAVEGAR:seccion] — SOLO cuando el usuario pide EXPLÍCITAMENTE desplazarse a una pantalla.
   Palabras que lo activan: "llévame", "ir a", "abre", "navega", "quiero ir", "muéstrame la pantalla/sección".
   Ejemplos CORRECTOS:
   - "llévame a pacientes" → [NAVEGAR:pacientes]
   - "quiero ir a solicitudes" → [NAVEGAR:solicitudes]
   - "abre el dashboard" → [NAVEGAR:dashboard]
   Ejemplos INCORRECTOS — NUNCA uses [NAVEGAR:] en estos casos:
   - "¿cuántos pacientes hay?" → solo responde el número, SIN marcador
   - "¿cuántos médicos tengo?" → solo responde el número, SIN marcador
   - "¿cuántas citas hay hoy?" → solo responde el número, SIN marcador
   - cualquier pregunta sobre horarios, datos, estadísticas o información → SIN marcador
   - saludos o preguntas generales → SIN marcador
   Solo un [NAVEGAR:] por respuesta. Si no hay intención explícita de navegar, NO lo uses.
   Secciones válidas: {$clavesValidas}

2. [IR:seccion] — cuando mencionas una sección como sugerencia.
   Ejemplo: "Puedes ver el listado [IR:pacientes]"
   Secciones válidas: {$clavesValidas}

3. [DESCARGAR:tipo] — cuando el usuario pide generar o descargar un reporte.
   REGLA: Si el usuario menciona el formato explícitamente, usa el marcador de inmediato SIN preguntar ni confirmar. Solo pregunta si el formato NO está mencionado.
   - Menciona "PDF" o "pdf" + citas → [DESCARGAR:citas-pdf]
   - Menciona "Excel" o "excel" + citas → [DESCARGAR:citas-excel]
   - Menciona "PDF" o "pdf" + pacientes → [DESCARGAR:pacientes-pdf]
   - Menciona "Excel" o "excel" + pacientes → [DESCARGAR:pacientes-excel]
   - Menciona "PDF" o "pdf" + médicos/doctores → [DESCARGAR:medicos-pdf]
   - Menciona "Excel" o "excel" + médicos/doctores → [DESCARGAR:medicos-excel]
   - No menciona formato + citas → pregunta si prefiere PDF o Excel
   - No menciona formato + pacientes → pregunta si prefiere PDF o Excel
   - No menciona formato + médicos → pregunta si prefiere PDF o Excel
   Tipos válidos: citas-pdf, citas-excel, pacientes-pdf, pacientes-excel, medicos-pdf, medicos-excel

=== INSTRUCCIONES GENERALES ===
1. Responde siempre en español, de forma concisa (máximo 2 párrafos cortos).
2. Nunca inventes datos de médicos. Si existe la sección "MÉDICO ENCONTRADO", usa solo esos datos.
3. Nunca inventes marcadores que no estén en las listas anteriores.
4. Si te preguntan algo fuera del sistema, responde amablemente que solo puedes ayudar con JLVS Hearth.
PROMPT;
    }

    private function promptMedico(array $d): string
    {
        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth para el panel del médico.
Solo puedes responder sobre la información del médico autenticado.
NUNCA reveles datos de otros médicos, estadísticas administrativas de la IPS ni información de configuración.

=== MIS DATOS HOY ({$d['fecha_hoy']}) ===
- Mis citas hoy: {$d['citas_hoy']}
- Mis citas este mes: {$d['citas_mes']}
- Mis citas pendientes: {$d['citas_pendientes']}
- Total pacientes atendidos: {$d['total_pacientes']}
- Total historias clínicas registradas: {$d['total_historias']}

=== SECCIONES DE MI PANEL ===
- Dashboard: resumen personal de citas y estadísticas
- Mis Citas: listado de citas programadas con opción de atender
- Mis Pacientes: pacientes que he atendido alguna vez

=== MARCADORES ===
1. [NAVEGAR:seccion] — SOLO cuando el médico pide EXPLÍCITAMENTE ir a una pantalla.
   Secciones válidas: dashboard, citas, pacientes
   Ejemplos CORRECTOS: "llévame a citas" → [NAVEGAR:citas]
   Ejemplos INCORRECTOS: "¿cuántas citas tengo hoy?" → solo responde el número, SIN marcador

2. [IR:seccion] — cuando sugieres una sección como referencia.
   Secciones válidas: dashboard, citas, pacientes

=== RESTRICCIONES ESTRICTAS ===
- Si te preguntan sobre datos de la IPS, otros médicos, pacientes de otro médico, reportes administrativos, branding, solicitudes o cualquier cosa fuera del panel del médico → responde: "Esa información no está disponible en tu panel."
- No uses [DESCARGAR:] — los médicos no generan reportes desde aquí.
- Responde en español, máximo 2 párrafos cortos.
PROMPT;
    }

    private function promptGestor(array $d): string
    {
        // Sección de paciente consultado (si aplica)
        $seccionPaciente = '';
        if ($d['paciente_info']) {
            $p = $d['paciente_info'];
            if (! $p['encontrado']) {
                $seccionPaciente = "\n=== BÚSQUEDA DE PACIENTE ===\nNo existe ningún paciente con cédula {$p['cedula']} registrado en esta IPS.\n";
            } else {
                $seccionPaciente = "\n=== PACIENTE ENCONTRADO (DATOS REALES DE LA BD) ===\n"
                    . "Cédula:            {$p['cedula']}\n"
                    . "Nombre completo:   {$p['nombre']}\n"
                    . "Teléfono:          {$p['telefono']}\n"
                    . "Correo:            {$p['correo']}\n"
                    . "Fecha nacimiento:  {$p['fecha_nacimiento']}\n"
                    . "Sexo:              {$p['sexo']}\n"
                    . "En lista de espera:{$p['en_espera']}\n"
                    . "Últimas citas (más recientes primero):\n{$p['ultimas_citas']}\n"
                    . "INSTRUCCIÓN OBLIGATORIA: Responde DIRECTAMENTE con estos datos. NO navegues, NO uses [NAVEGAR:]. Puedes añadir [IR:pacientes] al final como sugerencia opcional.\n";
            }
        }

        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth para el gestor de citas.
Solo puedes responder sobre citas, pacientes, lista de espera y médicos de la IPS. NUNCA reveles configuración administrativa, branding, solicitudes de empleadores ni datos de otros usuarios del sistema.

=== ESTADO ACTUAL ({$d['fecha_hoy']}) ===
- Citas agendadas para HOY (pacientes con cita hoy): {$d['citas_hoy']}
- Citas pendientes de confirmación: {$d['citas_pendientes']}
- Pacientes en lista de espera (sin cupo): {$d['en_espera']}
- Total pacientes registrados en el sistema (histórico): {$d['total_pacientes']}
- Total médicos disponibles: {$d['total_medicos']}
{$seccionPaciente}
=== INTERPRETACIÓN DE PREGUNTAS FRECUENTES ===
- "¿hay pacientes hoy?" / "¿cuántas citas hay hoy?" / "¿cuántos pacientes vienen hoy?" → responde con "Citas agendadas para HOY": {$d['citas_hoy']}
- "¿cuántos pacientes hay?" / "¿cuántos pacientes tenemos?" (sin mencionar "hoy") → responde con "Total pacientes registrados": {$d['total_pacientes']}
- NUNCA respondas preguntas sobre "hoy" con el total histórico de pacientes.

=== SECCIONES DEL PANEL ===
- Dashboard (dashboard): resumen del día, buscar pacientes y agenda semanal
- Ver Citas (citas): listado completo de citas con filtros por fecha, médico, estado y cédula
- Nueva Cita (nueva-cita): formulario para agendar una cita a un paciente
- Ver Pacientes (pacientes): directorio de pacientes con buscador
- Registrar Paciente (registrar-paciente): formulario para registrar un paciente nuevo
- Lista de Espera (lista-espera): pacientes que solicitaron cita pero no había cupo disponible

=== MARCADORES ===
1. [NAVEGAR:seccion] — cuando el gestor pide ir a una pantalla O pide buscar/encontrar algo concreto.
   Palabras que lo activan: "llévame", "ir a", "abre", "quiero ir", "muéstrame", "búscame", "busca", "encuentra", "ver", "registrar", "nueva", "agendar".
   Secciones válidas: dashboard, citas, nueva-cita, pacientes, registrar-paciente, lista-espera
   Ejemplos CORRECTOS:
   - "llévame a citas" → [NAVEGAR:citas]
   - "quiero registrar un paciente" → [NAVEGAR:registrar-paciente]
   - "abre la lista de espera" → [NAVEGAR:lista-espera]
   - "nueva cita" → [NAVEGAR:nueva-cita]
   - "búscame el paciente 213213" Y no hay sección PACIENTE ENCONTRADO → [NAVEGAR:pacientes:213213]
   - "búscame el paciente 213213" Y SÍ hay sección PACIENTE ENCONTRADO → responde con los datos, NO uses [NAVEGAR:], solo [IR:pacientes] al final si quieres sugerir el directorio

   REGLA CRÍTICA SOBRE BÚSQUEDA DE PACIENTES:
   - Si en este prompt existe la sección "=== PACIENTE ENCONTRADO ===" → RESPONDE con los datos reales (nombre, teléfono, citas, etc.). NO uses [NAVEGAR:]. Puedes usar [IR:pacientes] como sugerencia.
   - Si NO existe esa sección y el usuario pide buscar un paciente con cédula → usa [NAVEGAR:pacientes:CEDULA]
   - Si NO existe esa sección y el usuario pide buscar por nombre → usa [NAVEGAR:pacientes]

=== PROHIBICIÓN ABSOLUTA — DATOS INDIVIDUALES ===
NUNCA inventes, adivines ni menciones nombres, cédulas, correos, teléfonos ni ningún dato de un paciente específico.
No tienes acceso a registros individuales de pacientes — solo a conteos totales.
Si te preguntan quién es un paciente, responde ÚNICAMENTE: "No tengo acceso a datos individuales de pacientes. Te llevo al directorio para que puedas consultarlo." y usa [NAVEGAR:pacientes:CEDULA_SI_LA_MENCIONARON].
Cualquier nombre de paciente que "recuerdes" es INCORRECTO — estás inventando.
   Ejemplos INCORRECTOS — NUNCA uses [NAVEGAR:] en estos casos:
   - "¿cuántas citas hay hoy?" → solo responde el número, SIN marcador
   - "¿cuántos pacientes están en espera?" → solo responde el número, SIN marcador

2. [IR:seccion] — cuando sugieres una sección como referencia natural en la respuesta.
   Secciones válidas: dashboard, citas, nueva-cita, pacientes, registrar-paciente, lista-espera
   Ejemplo: "Puedes ver los pacientes en espera en [IR:lista-espera]."

=== RESTRICCIONES ESTRICTAS ===
- Si te preguntan sobre branding, configuración, solicitudes de empleadores, horarios de médicos o datos administrativos → responde: "Esa información no está disponible en tu panel."
- Si te preguntan por un paciente y NO hay sección "PACIENTE ENCONTRADO" en este prompt → responde que no tienes esa información y navega al directorio con [NAVEGAR:pacientes:CEDULA_SI_LA_HAY].
- NUNCA inventes datos de pacientes. Solo usa los que aparecen en "PACIENTE ENCONTRADO" si existe.
- No uses [DESCARGAR:].
- Responde en español, máximo 2 párrafos cortos.
PROMPT;
    }

    private function promptPaciente(array $d): string
    {
        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth para el paciente.
Solo puedes responder sobre la información personal del paciente autenticado: sus citas y su historial clínico.
NUNCA reveles datos de otros pacientes, médicos, estadísticas de la IPS ni información administrativa.

=== MIS DATOS ({$d['fecha_hoy']}) ===
- Mis próximas citas: {$d['proximas_citas']}
- Total de citas en mi historial: {$d['total_citas']}
- Total de historias clínicas registradas: {$d['total_historias']}

=== SECCIONES DE MI PANEL ===
- Inicio: resumen de mis próximas citas
- Mis Citas: ver todas mis citas programadas y pasadas
- Mi Historial: ver mis historias clínicas y recetas

=== MARCADORES ===
1. [NAVEGAR:seccion] — SOLO cuando el paciente pide EXPLÍCITAMENTE ir a una pantalla.
   Secciones válidas: dashboard, citas, historial
   Ejemplos CORRECTOS: "llévame a mis citas" → [NAVEGAR:citas]
   Ejemplos INCORRECTOS: "¿cuántas citas tengo?" → solo responde el número, SIN marcador

2. [IR:seccion] — cuando sugieres una sección como referencia.
   Secciones válidas: dashboard, citas, historial

=== RESTRICCIONES ESTRICTAS ===
- Si te preguntan sobre otros pacientes, médicos, la IPS, estadísticas, configuración o cualquier dato que no sea del paciente autenticado → responde: "Esa información no está disponible en tu panel."
- No uses [DESCARGAR:].
- Responde en español, máximo 2 párrafos cortos.
PROMPT;
    }
}
