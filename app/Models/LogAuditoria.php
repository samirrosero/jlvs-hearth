<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';

    // Solo created_at — los logs son inmutables
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'empresa_id',
        'accion',
        'modelo',
        'modelo_id',
        'ip',
        'detalles',
    ];

    protected function casts(): array
    {
        return [
            'detalles'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
