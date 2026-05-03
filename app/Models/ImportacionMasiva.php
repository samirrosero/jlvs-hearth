<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacionMasiva extends Model
{
    protected $table = 'importaciones_masivas';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
        'total_filas',
        'procesadas',
        'exitosas',
        'fallidas',
        'estado',
        'enviar_correos',
        'usuarios_creados',
        'errores',
        'iniciado_en',
        'finalizado_en',
        'mensaje_error',
    ];

    protected function casts(): array
    {
        return [
            'enviar_correos'  => 'boolean',
            'usuarios_creados' => 'array',
            'errores'         => 'array',
            'iniciado_en'     => 'datetime',
            'finalizado_en'   => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calcula el porcentaje de progreso.
     */
    public function getPorcentajeAttribute(): int
    {
        if ($this->total_filas === 0) return 0;
        return (int) round(($this->procesadas / $this->total_filas) * 100);
    }
}
