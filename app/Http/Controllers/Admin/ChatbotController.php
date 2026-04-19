<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => ['required', 'string', 'max:500'],
        ]);

        $empresaId = auth()->user()->empresa_id;
        $datos     = $this->recopilarDatos($empresaId);

        $respuesta = Http::timeout(60)->post('http://localhost:11434/api/chat', [
            'model'  => 'gemma3:4b',
            'stream' => false,
            'messages' => [
                ['role' => 'system', 'content' => $this->buildPrompt($datos)],
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

    private function recopilarDatos(int $empresaId): array
    {
        $hoy  = now()->toDateString();
        $datos = ['fecha_hoy' => now()->translatedFormat('l j \d\e F \d\e Y')];

        // Conteos dinámicos definidos en config/chatbot.php
        foreach (config('chatbot.datos') as $item) {
            $query = $item['model']::where('empresa_id', $empresaId);
            if (!empty($item['where'])) {
                $query->where($item['where']);
            }
            $datos[$item['clave']] = $query->count();
        }

        // Citas: lógica especial con relación a estado
        $datos['citas_hoy']        = Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count();
        $datos['citas_pendientes'] = Cita::where('empresa_id', $empresaId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))
            ->count();

        return $datos;
    }

    private function buildPrompt(array $d): string
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
}
