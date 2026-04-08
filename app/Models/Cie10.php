<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cie10 extends Model
{
    protected $table = 'cie10';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'categoria',
    ];
}
