<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcesarImportacionMasiva;
use App\Models\ImportacionMasiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminImportarController extends Controller
{
    /**
     * Vista principal de importación
     */
    public function index()
    {
        $tiposImportacion = [
            'pacientes' => [
                'titulo' => 'Pacientes',
                'descripcion' => 'Importar pacientes con su información básica y asignación de usuario.',
                'campos_requeridos' => ['nombre_completo', 'identificacion', 'correo', 'telefono'],
                'campos_opcionales' => ['fecha_nacimiento', 'sexo', 'direccion', 'tipo_documento', 'convenio'],
                'plantilla' => $this->generarPlantillaPacientes(),
            ],
            'medicos' => [
                'titulo' => 'Médicos',
                'descripcion' => 'Importar médicos con especialidad y horarios.',
                'campos_requeridos' => ['nombre_completo', 'identificacion', 'correo', 'telefono', 'especialidad', 'registro_medico'],
                'campos_opcionales' => ['tipo_documento'],
                'plantilla' => $this->generarPlantillaMedicos(),
            ],
            'gestores' => [
                'titulo' => 'Gestores de Citas',
                'descripcion' => 'Importar personal de gestión de citas.',
                'campos_requeridos' => ['nombre_completo', 'identificacion', 'correo', 'telefono'],
                'campos_opcionales' => ['tipo_documento'],
                'plantilla' => $this->generarPlantillaGestores(),
            ],
            'administradores' => [
                'titulo' => 'Administradores',
                'descripcion' => 'Importar usuarios administradores del sistema.',
                'campos_requeridos' => ['nombre_completo', 'identificacion', 'correo', 'telefono'],
                'campos_opcionales' => ['tipo_documento'],
                'plantilla' => $this->generarPlantillaAdministradores(),
            ],
        ];

        return view('admin.importar.index', compact('tiposImportacion'));
    }

    /**
     * Procesar importación - Crea registro y despacha Job para procesarlo en background
     */
    public function importar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:pacientes,medicos,gestores,administradores',
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $archivo = $request->file('archivo');
            $empresaId = auth()->user()->empresa_id;

            // Guardar archivo en storage temporal
            $nombreArchivo = $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs(
                "importaciones/{$empresaId}",
                Str::random(20) . '.' . $archivo->getClientOriginalExtension(),
                'local'
            );

            // Crear registro de importación
            $importacion = ImportacionMasiva::create([
                'empresa_id' => $empresaId,
                'user_id' => auth()->id(),
                'tipo' => $request->tipo,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'enviar_correos' => $request->boolean('enviar_correos', true),
                'estado' => 'pendiente',
            ]);

            // Despachar Job en background
            ProcesarImportacionMasiva::dispatch($importacion->id);

            return redirect()->route('admin.importar.progreso', $importacion->id)
                ->with('exito', 'Importación iniciada. Puedes navegar libremente mientras se procesa.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al iniciar importación: ' . $e->getMessage());
        }
    }

    /**
     * Vista con barra de progreso - se actualiza en tiempo real
     */
    public function progreso(ImportacionMasiva $importacion)
    {
        abort_unless($importacion->empresa_id === auth()->user()->empresa_id, 403);
        return view('admin.importar.progreso', compact('importacion'));
    }

    /**
     * Endpoint JSON para polling de progreso
     */
    public function estado(ImportacionMasiva $importacion)
    {
        abort_unless($importacion->empresa_id === auth()->user()->empresa_id, 403);

        return response()->json([
            'id' => $importacion->id,
            'estado' => $importacion->estado,
            'total' => $importacion->total_filas,
            'procesadas' => $importacion->procesadas,
            'exitosas' => $importacion->exitosas,
            'fallidas' => $importacion->fallidas,
            'porcentaje' => $importacion->porcentaje,
            'mensaje_error' => $importacion->mensaje_error,
            'iniciado_en' => $importacion->iniciado_en?->format('d/m/Y H:i:s'),
            'finalizado_en' => $importacion->finalizado_en?->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Mostrar resultados detallados de importación completada
     */
    public function resultados(ImportacionMasiva $importacion)
    {
        abort_unless($importacion->empresa_id === auth()->user()->empresa_id, 403);

        return view('admin.importar.resultados', compact('importacion'));
    }

    /**
     * Historial de importaciones
     */
    public function historial()
    {
        $importaciones = ImportacionMasiva::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.importar.historial', compact('importaciones'));
    }

    /**
     * Descargar plantilla de importación
     */
    public function descargarPlantilla($tipo)
    {
        $plantillas = [
            'pacientes' => $this->generarPlantillaPacientes(),
            'medicos' => $this->generarPlantillaMedicos(),
            'gestores' => $this->generarPlantillaGestores(),
            'administradores' => $this->generarPlantillaAdministradores(),
        ];

        if (!isset($plantillas[$tipo])) {
            abort(404);
        }

        $nombreArchivo = "plantilla_".$tipo."_".date('Y-m-d').'.csv';

        $csvContent = $plantillas[$tipo];

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response($csvContent, 200, $headers);
    }

    /**
     * Generar CSV de plantilla para pacientes
     */
    private function generarPlantillaPacientes(): string
    {
        $filas = [
            ['tipo_documento','identificacion','nombre_completo','fecha_nacimiento','sexo','telefono','correo','direccion','convenio'],
            ['CC','1234567890','Juan Carlos Pérez García','1985-03-15','M','3001234567','juan.perez@email.com','Calle 123 #45-67','Particular'],
            ['CC','9876543210','María Elena Rodríguez','1990-07-22','F','3109876543','maria.rodriguez@email.com','Carrera 45 #12-34','Medicina Prepagada'],
            ['CE','1122334455','Pedro Antonio López','1978-11-30','M','3201122334','pedro.lopez@email.com','Avenida 67 #89-01','Particular'],
            ['TI','5566778899','Ana Lucía Martínez','2005-01-10','F','3155566778','ana.martinez@email.com','Calle 89 #23-45','Particular'],
        ];
        return $this->arrayACsv($filas);
    }

    /**
     * Generar CSV de plantilla para médicos
     */
    private function generarPlantillaMedicos(): string
    {
        $filas = [
            ['tipo_documento','identificacion','nombre_completo','especialidad','registro_medico','telefono','correo'],
            ['CC','1234567891','Dra. Laura García López','Medicina General','RM-12345','3001234568','laura.garcia@clinica.com'],
            ['CC','9876543211','Dr. Carlos Alberto Torres','Pediatría','RM-67890','3109876544','carlos.torres@clinica.com'],
            ['CC','1122334456','Dra. Ana María Fernández','Cardiología','RM-11111','3201122335','ana.fernandez@clinica.com'],
        ];
        return $this->arrayACsv($filas);
    }

    /**
     * Generar CSV de plantilla para gestores
     */
    private function generarPlantillaGestores(): string
    {
        $filas = [
            ['tipo_documento','identificacion','nombre_completo','telefono','correo'],
            ['CC','1234567892','Carmen Elena Vargas','3001234569','carmen.vargas@clinica.com'],
            ['CC','9876543212','Roberto Carlos Mendoza','3109876545','roberto.mendoza@clinica.com'],
        ];
        return $this->arrayACsv($filas);
    }

    /**
     * Generar CSV de plantilla para administradores
     */
    private function generarPlantillaAdministradores(): string
    {
        $filas = [
            ['tipo_documento','identificacion','nombre_completo','telefono','correo'],
            ['CC','1234567893','Administrador Principal','3001234570','admin@clinica.com'],
            ['CC','9876543213','Sub Administrador','3109876546','subadmin@clinica.com'],
        ];
        return $this->arrayACsv($filas);
    }

    /**
     * Convierte un array bidimensional a CSV correctamente formateado para Excel.
     */
    private function arrayACsv(array $filas): string
    {
        $output = fopen('php://temp', 'r+');

        // Directiva para forzar a Excel a usar coma como separador (ignora config regional)
        fwrite($output, "sep=,\r\n");

        foreach ($filas as $fila) {
            fputcsv($output, $fila, ',', '"', '\\');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Agregar BOM UTF-8 al inicio para que Excel detecte correctamente la codificación
        return "\xEF\xBB\xBF" . $csv;
    }
}
