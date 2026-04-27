<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecioServicio extends Model
{
    protected $table = 'precios_servicio';

    protected $fillable = [
        'empresa_id',
        'servicio_id',
        'portafolio_id',
        'precio',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function portafolio(): BelongsTo
    {
        return $this->belongsTo(Portafolio::class);
    }
}
