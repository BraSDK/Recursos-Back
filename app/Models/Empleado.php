<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';

    public function puesto() {
        return $this->belongsTo(Puesto::class);
    }

    public function jefe() {
        return $this->belongsTo(Empleado::class, 'jefe_id');
    }
}
