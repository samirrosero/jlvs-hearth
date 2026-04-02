<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoAdjunto extends Model
{
    protected $table = 'documentos_adjuntos';

    protected $fillable = [
        'historia_clinica_id',
        'nombre_archivo',
        'ruta_almacenamiento',
        'tipo_mime',
    ];

    // Un documento adjunto pertenece a una historia clínica
    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }
}
