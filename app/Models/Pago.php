<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'empresa_id',
        'cita_id',
        'paciente_id',
        'monto',
        'metodo_pago', // efectivo, tarjeta, transferencia, prepagada, etc.
        'estado', // pendiente, pagado, reembolsado, cancelado
        'tipo_pago', // presencial, telemedicina
        'referencia', // número de transacción, comprobante
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
