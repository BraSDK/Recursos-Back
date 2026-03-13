<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $table = 'puestos';

    public function departamento() {
        return $this->belongsTo(Departamento::class);
    }
}
