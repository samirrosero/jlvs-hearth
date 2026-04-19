<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => ['required', 'string', 'max:500'],
        ]);

        $rol    = auth()->user()->rol?->nombre ?? 'administrador';
        $datos  = $this->recopilarDatos($rol);
        $prompt = $this->buildPrompt($rol, $datos);

        $respuesta = Http::timeout(60)->post('http://localhost:11434/api/chat', [
            'model'  => 'gemma3:4b',
            'stream' => false,
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user',   'content' => $request->input('mensaje')],
            ],
        ]);

        if ($respuesta->failed()) {
            return response()->json([
                'error' => 'No se pudo conectar con el asistente. Asegúrate de que Ollama esté corriendo.',
            ], 503);
        }

        $texto = $respuesta->json('message.content') ?? 'Sin respuesta del modelo.';

        return response()->json(['respuesta' => $texto]);
    }

    // ─────────────────────────────────────────────────────────────
    // Recopilación de datos por rol
    // ─────────────────────────────────────────────────────────────

    private function recopilarDatos(string $rol): array
    {
        $hoy = now()->toDateString();

        return match ($rol) {
            'medico'       => $this->datosRolMedico($hoy),
            'gestor_citas' => $this->datosRolGestor($hoy),
            'paciente'     => $this->datosRolPaciente($hoy),
            default        => $this->datosRolAdmin($hoy),
        };
    }

    private function datosRolAdmin(string $hoy): array
    {
        $empresaId = auth()->user()->empresa_id;
        $datos = ['fecha_hoy' => now()->translatedFormat('l j \d\e F \d\e Y')];

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

    private function datosRolGestor(string $hoy): array
    {
        $empresaId = auth()->user()->empresa_id;

        return [
            'fecha_hoy'         => now()->translatedFormat('l j \d\e F \d\e Y'),
            'citas_hoy'         => Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count(),
            'citas_pendientes'  => Cita::where('empresa_id', $empresaId)
                                        ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
                                        ->count(),
            'total_pacientes'   => \App\Models\Paciente::where('empresa_id', $empresaId)->count(),
            'total_medicos'     => \App\Models\Medico::where('empresa_id', $empresaId)->count(),
        ];
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

        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth, software médico para IPS colombianas.
Ayudas al administrador a entender el estado del sistema y a navegar por él.

=== DATOS ACTUALES DE LA IPS ===
{$listaDatos}
- Citas programadas para hoy ({$d['fecha_hoy']}): {$d['citas_hoy']}
- Citas pendientes: {$d['citas_pendientes']}

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
   - cualquier pregunta sobre datos, estadísticas o información → SIN marcador
   - saludos o preguntas generales → SIN marcador
   Solo un [NAVEGAR:] por respuesta. Si no hay intención explícita de navegar, NO lo uses.
   Secciones válidas: {$clavesValidas}

2. [IR:seccion] — cuando mencionas una sección como sugerencia.
   Ejemplo: "Puedes ver el listado [IR:pacientes]"
   Secciones válidas: {$clavesValidas}

3. [DESCARGAR:tipo] — cuando el usuario pide generar o descargar un reporte.
   - "reporte de citas en PDF" → [DESCARGAR:citas-pdf]
   - "reporte de citas en Excel" → [DESCARGAR:citas-excel]
   - "reporte de pacientes en PDF" → [DESCARGAR:pacientes-pdf]
   - "reporte de pacientes en Excel" → [DESCARGAR:pacientes-excel]
   - Si el usuario no especifica formato, pregúntale si prefiere PDF o Excel.
   Tipos válidos: citas-pdf, citas-excel, pacientes-pdf, pacientes-excel

=== INSTRUCCIONES GENERALES ===
1. Responde siempre en español, de forma concisa (máximo 2 párrafos cortos).
2. Nunca inventes marcadores que no estén en las listas anteriores.
3. Si te preguntan algo fuera del sistema, responde amablemente que solo puedes ayudar con JLVS Hearth.
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
        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth para el gestor de citas.
Solo puedes responder sobre citas, pacientes y médicos de la IPS. NUNCA reveles configuración administrativa, branding, solicitudes de empleadores ni datos de otros usuarios del sistema.

=== ESTADO ACTUAL ({$d['fecha_hoy']}) ===
- Citas programadas hoy: {$d['citas_hoy']}
- Citas pendientes de confirmación: {$d['citas_pendientes']}
- Total pacientes en el sistema: {$d['total_pacientes']}
- Total médicos disponibles: {$d['total_medicos']}

=== SECCIONES DEL PANEL ===
- Dashboard: resumen del día y estadísticas de citas
- Citas: agendar, modificar y gestionar citas
- Pacientes: buscar y registrar pacientes

=== MARCADORES ===
1. [NAVEGAR:seccion] — SOLO cuando el gestor pide EXPLÍCITAMENTE ir a una pantalla.
   Secciones válidas: dashboard, citas, pacientes
   Ejemplos CORRECTOS: "llévame a citas" → [NAVEGAR:citas]
   Ejemplos INCORRECTOS: "¿cuántas citas hay hoy?" → solo responde el número, SIN marcador

2. [IR:seccion] — cuando sugieres una sección como referencia.
   Secciones válidas: dashboard, citas, pacientes

=== RESTRICCIONES ESTRICTAS ===
- Si te preguntan sobre branding, configuración, solicitudes de empleadores, horarios de médicos o datos administrativos → responde: "Esa información no está disponible en tu panel."
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
