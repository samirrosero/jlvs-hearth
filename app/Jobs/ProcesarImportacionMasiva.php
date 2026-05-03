<?php

namespace App\Jobs;

use App\Mail\CredencialesImportacionMail;
use App\Models\ImportacionMasiva;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcesarImportacionMasiva implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hora para grandes volúmenes
    public int $tries = 1;

    public function __construct(public int $importacionId)
    {
    }

    public function handle(): void
    {
        $importacion = ImportacionMasiva::find($this->importacionId);
        if (!$importacion) return;

        $importacion->update([
            'estado' => 'procesando',
            'iniciado_en' => now(),
        ]);

        try {
            $rutaCompleta = Storage::disk('local')->path($importacion->ruta_archivo);
            
            if (!file_exists($rutaCompleta)) {
                throw new \Exception('Archivo no encontrado: ' . $rutaCompleta);
            }

            // Leer el archivo con PhpSpreadsheet
            $spreadsheet = IOFactory::load($rutaCompleta);
            $hoja = $spreadsheet->getActiveSheet();
            $filas = $hoja->toArray(null, true, true, false);

            if (empty($filas)) {
                throw new \Exception('El archivo está vacío');
            }

            // Primera fila son los encabezados
            $encabezados = array_map(fn($h) => strtolower(trim((string) $h)), $filas[0]);
            $filasDatos = array_slice($filas, 1);
            
            // Filtrar filas vacías
            $filasDatos = array_filter($filasDatos, function($fila) {
                return !empty(array_filter($fila, fn($v) => $v !== null && $v !== ''));
            });

            $importacion->update(['total_filas' => count($filasDatos)]);

            $rol = $this->obtenerRol($importacion->tipo);
            $portafolioParticular = Portafolio::where('empresa_id', $importacion->empresa_id)
                ->where('nombre_convenio', 'Particular')
                ->first();

            $usuariosCreados = [];
            $errores = [];
            $exitosas = 0;
            $fallidas = 0;
            $procesadas = 0;

            foreach ($filasDatos as $indice => $fila) {
                $numeroFila = $indice + 2; // +2 porque array es 0-index y encabezados es fila 1

                try {
                    // Mapear fila a array asociativo
                    $datos = [];
                    foreach ($encabezados as $i => $campo) {
                        $datos[$campo] = isset($fila[$i]) ? trim((string) $fila[$i]) : null;
                    }

                    DB::transaction(function () use ($datos, $rol, $portafolioParticular, $importacion, &$usuariosCreados) {
                        $this->procesarFila($datos, $rol, $portafolioParticular, $importacion, $usuariosCreados);
                    });

                    $exitosas++;
                } catch (\Exception $e) {
                    $fallidas++;
                    $errores[] = [
                        'fila' => $numeroFila,
                        'error' => $e->getMessage(),
                        'datos' => $fila,
                    ];
                    Log::warning("Importación #{$importacion->id} - Fila {$numeroFila}: " . $e->getMessage());
                }

                $procesadas++;

                // Actualizar progreso cada 5 filas (o al final) para no saturar la BD
                if ($procesadas % 5 === 0 || $procesadas === count($filasDatos)) {
                    $importacion->update([
                        'procesadas' => $procesadas,
                        'exitosas' => $exitosas,
                        'fallidas' => $fallidas,
                    ]);
                }
            }

            // Actualización final
            $importacion->update([
                'procesadas' => $procesadas,
                'exitosas' => $exitosas,
                'fallidas' => $fallidas,
                'usuarios_creados' => $usuariosCreados,
                'errores' => $errores,
                'estado' => 'completada',
                'finalizado_en' => now(),
            ]);

            // Enviar correos si aplica
            if ($importacion->enviar_correos && !empty($usuariosCreados)) {
                $this->enviarCorreos($usuariosCreados, $importacion->empresa_id);
            }

            // Limpiar archivo temporal
            Storage::disk('local')->delete($importacion->ruta_archivo);

        } catch (\Exception $e) {
            Log::error('Error en importación #' . $importacion->id . ': ' . $e->getMessage());
            $importacion->update([
                'estado' => 'fallida',
                'mensaje_error' => $e->getMessage(),
                'finalizado_en' => now(),
            ]);
        }
    }

    private function procesarFila(array $datos, Rol $rol, ?Portafolio $portafolio, ImportacionMasiva $importacion, array &$usuariosCreados): void
    {
        $identificacion = $this->limpiar($datos['identificacion'] ?? $datos['cedula'] ?? null);
        $nombre = $this->limpiar($datos['nombre_completo'] ?? $datos['nombre'] ?? null);
        $correo = $this->limpiar($datos['correo'] ?? $datos['email'] ?? null);

        if (!$identificacion || !$nombre || !$correo) {
            throw new \Exception('Faltan campos obligatorios (identificacion, nombre_completo, correo)');
        }

        // Validar formato de email
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Correo inválido: {$correo}");
        }

        // Verificar duplicados
        $existe = User::where('email', $correo)
            ->orWhere(function ($q) use ($identificacion, $importacion) {
                $q->where('identificacion', $identificacion)
                  ->where('empresa_id', $importacion->empresa_id);
            })
            ->exists();

        if ($existe) {
            throw new \Exception("Ya existe usuario con correo {$correo} o identificación {$identificacion}");
        }

        // Generar contraseña temporal
        $passwordTemporal = $this->generarPassword();

        // Crear usuario
        $usuario = User::create([
            'empresa_id' => $importacion->empresa_id,
            'rol_id' => $rol->id,
            'nombre' => $nombre,
            'email' => $correo,
            'identificacion' => $identificacion,
            'password' => Hash::make($passwordTemporal),
            'activo' => true,
        ]);

        // Crear registro específico según tipo
        switch ($importacion->tipo) {
            case 'pacientes':
                $this->crearPaciente($usuario, $datos, $portafolio, $importacion->empresa_id);
                break;
            case 'medicos':
                $this->crearMedico($usuario, $datos, $importacion->empresa_id);
                break;
        }

        $usuariosCreados[] = [
            'nombre' => $nombre,
            'correo' => $correo,
            'password_temporal' => $passwordTemporal,
            'rol' => $rol->nombre,
        ];
    }

    private function crearPaciente(User $usuario, array $datos, ?Portafolio $portafolio, int $empresaId): void
    {
        $convenio = $this->limpiar($datos['convenio'] ?? 'Particular');
        $portafolioId = $portafolio?->id;

        if ($convenio && $convenio !== 'Particular') {
            $p = Portafolio::where('empresa_id', $empresaId)
                ->where('nombre_convenio', 'like', '%' . $convenio . '%')
                ->first();
            if ($p) $portafolioId = $p->id;
        }

        Paciente::create([
            'usuario_id' => $usuario->id,
            'empresa_id' => $empresaId,
            'identificacion' => $usuario->identificacion,
            'nombre_completo' => $usuario->nombre,
            'fecha_nacimiento' => $this->parsearFecha($datos['fecha_nacimiento'] ?? null),
            'sexo' => $this->limpiar($datos['sexo'] ?? 'Otro'),
            'telefono' => $this->limpiar($datos['telefono'] ?? null),
            'correo' => $usuario->email,
            'direccion' => $this->limpiar($datos['direccion'] ?? null),
            'portafolio_id' => $portafolioId,
        ]);
    }

    private function crearMedico(User $usuario, array $datos, int $empresaId): void
    {
        Medico::create([
            'usuario_id' => $usuario->id,
            'empresa_id' => $empresaId,
            'especialidad' => $this->limpiar($datos['especialidad'] ?? 'Medicina General'),
            'registro_medico' => $this->limpiar($datos['registro_medico'] ?? ('RM-' . rand(10000, 99999))),
            'activo' => true,
        ]);
    }

    private function obtenerRol(string $tipo): Rol
    {
        $nombre = match($tipo) {
            'pacientes' => 'paciente',
            'medicos' => 'medico',
            'gestores' => 'gestor_citas',
            'administradores' => 'administrador',
            default => 'paciente',
        };

        return Rol::where('nombre', $nombre)->firstOrFail();
    }

    private function generarPassword(): string
    {
        return strtoupper(Str::random(4)) . rand(1000, 9999);
    }

    private function limpiar(?string $valor): ?string
    {
        return $valor === null ? null : trim($valor);
    }

    private function parsearFecha($fecha): ?string
    {
        if (empty($fecha)) return null;

        // Número de Excel (serial date)
        if (is_numeric($fecha)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $fecha))->format('Y-m-d');
            } catch (\Exception $e) {
                // Continuar con otros formatos
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'] as $formato) {
            try {
                return Carbon::createFromFormat($formato, (string) $fecha)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function enviarCorreos(array $usuarios, int $empresaId): void
    {
        foreach ($usuarios as $u) {
            try {
                Mail::to($u['correo'])->send(new CredencialesImportacionMail(
                    $u['nombre'], $u['correo'], $u['password_temporal'], $u['rol']
                ));
            } catch (\Exception $e) {
                Log::warning("Error enviando correo a {$u['correo']}: " . $e->getMessage());
            }
        }
    }
}
