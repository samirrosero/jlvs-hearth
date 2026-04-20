<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListaEspera extends Model
{
    protected $table = 'lista_espera';

    protected $fillable = [
        'empresa_id',
        'paciente_id',
        'medico_id',
        'servicio_id',
        'fecha_solicitada',
        'estado',
        'notas',
        'cita_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitada' => 'date',
        ];
    }

    public function empresa(): BelongsTo   { return $this->belongsTo(Empresa::class); }
    public function paciente(): BelongsTo  { return $this->belongsTo(Paciente::class); }
    public function medico(): BelongsTo    { return $this->belongsTo(Medico::class); }
    public function servicio(): BelongsTo  { return $this->belongsTo(Servicio::class); }
    public function cita(): BelongsTo      { return $this->belongsTo(Cita::class); }
}
