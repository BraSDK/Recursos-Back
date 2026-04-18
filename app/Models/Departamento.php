<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    // ¡Añade esto! Sin esto, Laravel no deja guardar datos
    protected $fillable = [
        'nombre',
        'codigo_dep',
        'area_general'
    ];
}
