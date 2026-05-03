<?php

namespace App\Imports;

use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Portafolio;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class UsuariosImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private string $tipo;
    private int $empresaId;
    private bool $enviarCorreos;
    private array $resultados = [
        'exitosos' => 0,
        'fallidos' => 0,
        'errores' => [],
        'usuarios_creados' => [],
    ];

    public function __construct(string $tipo, int $empresaId, bool $enviarCorreos = true)
    {
        $this->tipo = $tipo;
        $this->empresaId = $empresaId;
        $this->enviarCorreos = $enviarCorreos;
    }

    public function collection(Collection $rows)
    {
        $rol = $this->obtenerRol();
        $portafolioParticular = Portafolio::where('empresa_id', $this->empresaId)
            ->where('nombre_convenio', 'Particular')
            ->first();

        foreach ($rows as $index => $row) {
            try {
                DB::transaction(function () use ($row, $rol, $portafolioParticular, $index) {
                    $this->procesarFila($row, $rol, $portafolioParticular, $index + 2);
                });
            } catch (\Exception $e) {
                $this->resultados['fallidos']++;
                $this->resultados['errores'][] = [
                    'fila' => $index + 2,
                    'error' => $e->getMessage(),
                    'datos' => $row->toArray(),
                ];
                Log::error('Error importando fila ' . ($index + 2) . ': ' . $e->getMessage());
            }
        }

        // Enviar correos masivos si se solicitó
        if ($this->enviarCorreos && !empty($this->resultados['usuarios_creados'])) {
            $this->enviarCorreosMasivos();
        }
    }

    private function procesarFila($row, Rol $rol, ?Portafolio $portafolio, int $numeroFila)
    {
        // Validar campos obligatorios
        $identificacion = $this->limpiarCampo($row['identificacion'] ?? $row['Identificacion'] ?? null);
        $nombre = $this->limpiarCampo($row['nombre_completo'] ?? $row['Nombre Completo'] ?? $row['nombre'] ?? null);
        $correo = $this->limpiarCampo($row['correo'] ?? $row['Correo'] ?? $row['email'] ?? null);
        $telefono = $this->limpiarCampo($row['telefono'] ?? $row['Telefono'] ?? $row['Teléfono'] ?? null);

        if (!$identificacion || !$nombre || !$correo) {
            throw new \Exception('Faltan campos obligatorios (identificacion, nombre_completo, correo)');
        }

        // Generar contraseña temporal
        $passwordTemporal = $this->generarPasswordTemporal();

        // Verificar si el usuario ya existe
        $usuarioExistente = User::where('email', $correo)
            ->orWhere('identificacion', $identificacion)
            ->first();

        if ($usuarioExistente) {
            throw new \Exception("Usuario ya existe: {$correo} / {$identificacion}");
        }

        // Crear usuario
        $usuario = User::create([
            'empresa_id' => $this->empresaId,
            'rol_id' => $rol->id,
            'nombre' => $nombre,
            'email' => $correo,
            'identificacion' => $identificacion,
            'password' => Hash::make($passwordTemporal),
            'activo' => true,
            'debe_cambiar_password' => true,
        ]);

        // Crear registro específico según el tipo
        switch ($this->tipo) {
            case 'pacientes':
                $this->crearPaciente($usuario, $row, $portafolio);
                break;
            case 'medicos':
                $this->crearMedico($usuario, $row);
                break;
            case 'gestores':
            case 'administradores':
                // Solo se crea el usuario
                break;
        }

        // Guardar para envío de correos
        $this->resultados['usuarios_creados'][] = [
            'nombre' => $nombre,
            'correo' => $correo,
            'password_temporal' => $passwordTemporal,
            'rol' => $rol->nombre,
        ];

        $this->resultados['exitosos']++;
    }

    private function crearPaciente(User $usuario, $row, ?Portafolio $portafolio)
    {
        $tipoDocumento = $this->limpiarCampo($row['tipo_documento'] ?? $row['Tipo Documento'] ?? 'CC');
        $fechaNacimiento = $this->parsearFecha($row['fecha_nacimiento'] ?? $row['Fecha Nacimiento'] ?? null);
        $sexo = $this->limpiarCampo($row['sexo'] ?? $row['Sexo'] ?? 'Otro');
        $direccion = $this->limpiarCampo($row['direccion'] ?? $row['Direccion'] ?? $row['Dirección'] ?? null);
        
        // Determinar portafolio según convenio
        $convenioNombre = $this->limpiarCampo($row['convenio'] ?? $row['Convenio'] ?? 'Particular');
        $portafolioId = $portafolio?->id;
        
        if ($convenioNombre !== 'Particular') {
            $portafolioEncontrado = Portafolio::where('empresa_id', $this->empresaId)
                ->where('nombre_convenio', 'like', '%' . $convenioNombre . '%')
                ->first();
            if ($portafolioEncontrado) {
                $portafolioId = $portafolioEncontrado->id;
            }
        }

        Paciente::create([
            'usuario_id' => $usuario->id,
            'empresa_id' => $this->empresaId,
            'tipo_documento' => $tipoDocumento,
            'identificacion' => $usuario->identificacion,
            'nombre_completo' => $usuario->nombre,
            'fecha_nacimiento' => $fechaNacimiento,
            'sexo' => $sexo,
            'telefono' => $this->limpiarCampo($row['telefono'] ?? $row['Teléfono'] ?? null),
            'correo' => $usuario->email,
            'direccion' => $direccion,
            'portafolio_id' => $portafolioId,
        ]);
    }

    private function crearMedico(User $usuario, $row)
    {
        $especialidad = $this->limpiarCampo($row['especialidad'] ?? $row['Especialidad'] ?? 'Medicina General');
        $registroMedico = $this->limpiarCampo($row['registro_medico'] ?? $row['Registro Medico'] ?? $row['Registro Médico'] ?? 'RM-' . rand(10000, 99999));

        Medico::create([
            'usuario_id' => $usuario->id,
            'empresa_id' => $this->empresaId,
            'especialidad' => $especialidad,
            'registro_medico' => $registroMedico,
            'activo' => true,
        ]);
    }

    private function obtenerRol(): Rol
    {
        $rolNombre = match($this->tipo) {
            'pacientes' => 'paciente',
            'medicos' => 'medico',
            'gestores' => 'gestor_citas',
            'administradores' => 'administrador',
            default => 'paciente',
        };

        return Rol::where('nombre', $rolNombre)->firstOrFail();
    }

    private function generarPasswordTemporal(): string
    {
        // Formato: 4 letras mayúsculas + 4 números
        $letras = strtoupper(Str::random(4));
        $numeros = rand(1000, 9999);
        return $letras . $numeros;
    }

    private function limpiarCampo(?string $valor): ?string
    {
        if (is_null($valor)) return null;
        return trim($valor);
    }

    private function parsearFecha($fecha): ?string
    {
        if (is_null($fecha) || $fecha === '') return null;
        
        // Intentar diferentes formatos
        $formatos = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        
        foreach ($formatos as $formato) {
            try {
                $carbon = Carbon::createFromFormat($formato, $fecha);
                return $carbon->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Si es un número de Excel (serial date)
        if (is_numeric($fecha)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha))->format('Y-m-d');
            } catch (\Exception $e) {
                // Ignorar y retornar null
            }
        }
        
        return null;
    }

    private function enviarCorreosMasivos()
    {
        // Aquí puedes implementar envío asíncrono con colas
        // Por ahora, lo hacemos directo pero podría ser un Job
        foreach ($this->resultados['usuarios_creados'] as $usuario) {
            try {
                \Mail::to($usuario['correo'])->queue(new \App\Mail\CredencialesImportacionMail(
                    $usuario['nombre'],
                    $usuario['correo'],
                    $usuario['password_temporal'],
                    $usuario['rol']
                ));
            } catch (\Exception $e) {
                Log::error('Error enviando correo a ' . $usuario['correo'] . ': ' . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'identificacion' => 'required|string|max:20',
            'nombre_completo' => 'required|string|max:150',
            'correo' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
        ];
    }

    public function getResultados(): array
    {
        return $this->resultados;
    }

    public function onError(\Throwable $e)
    {
        Log::error('Error en importación: ' . $e->getMessage());
    }
}
