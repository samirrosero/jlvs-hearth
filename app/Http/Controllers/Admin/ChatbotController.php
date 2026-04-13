<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
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
                ['role' => 'system',  'content' => $this->buildPrompt($datos)],
                ['role' => 'user',    'content' => $request->input('mensaje')],
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
        $hoy = now()->toDateString();

        return [
            'total_pacientes'      => Paciente::where('empresa_id', $empresaId)->count(),
            'total_medicos'        => Medico::where('empresa_id', $empresaId)->count(),
            'citas_hoy'            => Cita::where('empresa_id', $empresaId)->where('fecha', $hoy)->count(),
            'citas_pendientes'     => Cita::where('empresa_id', $empresaId)->whereHas('estado', fn ($q) => $q->where('nombre', 'like', '%pendiente%'))->count(),
            'fecha_hoy'            => now()->translatedFormat('l j \d\e F \d\e Y'),
        ];
    }

    private function buildPrompt(array $d): string
    {
        return <<<PROMPT
Eres el asistente virtual de JLVS Hearth, software médico para IPS colombianas.
Ayudas al administrador a entender el estado del sistema y a navegar por él.

=== DATOS ACTUALES DE LA IPS ===
- Pacientes registrados: {$d['total_pacientes']}
- Médicos registrados: {$d['total_medicos']}
- Citas programadas para hoy ({$d['fecha_hoy']}): {$d['citas_hoy']}
- Citas pendientes: {$d['citas_pendientes']}

=== SECCIONES DE LA PLATAFORMA ===
- Dashboard: métricas generales
- Pacientes: listado, registro y edición
- Médicos: listado, registro y edición
- Reportes: PDF y Excel de citas y pacientes

=== MARCADORES DE NAVEGACIÓN ===
Tienes dos tipos de marcadores:

1. [NAVEGAR:seccion] — úsalo cuando el usuario PIDE IR a una sección o quiere que le lleves ahí.
   Ejemplos de cuándo usarlo:
   - "muéstrame los pacientes" → [NAVEGAR:pacientes]
   - "llévame a médicos" → [NAVEGAR:medicos]
   - "quiero ver los reportes" → [NAVEGAR:reportes]
   - "abre el dashboard" → [NAVEGAR:dashboard]
   Solo pon UN marcador [NAVEGAR:] por respuesta, al final del mensaje.

2. [IR:seccion] — úsalo cuando MENCIONAS una sección como sugerencia, sin que el usuario haya pedido ir ahí.
   Ejemplo: "Puedes consultar el listado de pacientes [IR:pacientes] para más detalle."

Secciones válidas: dashboard, pacientes, medicos, reportes

=== INSTRUCCIONES GENERALES ===
1. Responde siempre en español, de forma concisa (máximo 2 párrafos cortos).
2. Nunca inventes secciones que no estén en la lista.
3. Si te preguntan algo fuera del sistema, responde amablemente que solo puedes ayudar con JLVS Hearth.
PROMPT;
    }
}
