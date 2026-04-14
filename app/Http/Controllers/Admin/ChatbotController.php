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

=== MARCADORES ===
Tienes tres tipos de marcadores. Úsalos al final de la oración relevante:

1. [NAVEGAR:seccion] — cuando el usuario pide IR a una sección.
   - "muéstrame los pacientes" → [NAVEGAR:pacientes]
   - "llévame a médicos" → [NAVEGAR:medicos]
   - "abre el dashboard" → [NAVEGAR:dashboard]
   Solo un [NAVEGAR:] por respuesta.
   Secciones válidas: dashboard, pacientes, medicos, reportes

2. [IR:seccion] — cuando mencionas una sección como sugerencia.
   Ejemplo: "Puedes ver el listado [IR:pacientes]"
   Secciones válidas: dashboard, pacientes, medicos, reportes

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
