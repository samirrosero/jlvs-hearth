<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'usuario_id',
        'empresa_id',
        'nombre_completo',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'correo',
        'direccion',
        'identificacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    // Un paciente puede tener una cuenta de usuario (opcional)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Un paciente pertenece a una empresa (IPS)
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Un paciente puede tener muchas citas
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    // Un paciente puede tener muchas historias clínicas
    public function historiasClinicas(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'paciente_id');
    }

    // Un paciente puede tener muchos antecedentes médicos
    public function antecedentes(): HasMany
    {
        return $this->hasMany(AntecedentesPaciente::class, 'paciente_id');
    }

    // Un paciente puede tener muchos signos vitales registrados
    public function signosVitales(): HasMany
    {
        return $this->hasMany(SignosVitales::class, 'paciente_id');
    }

    // Si el paciente tiene cuenta, accede a su usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
