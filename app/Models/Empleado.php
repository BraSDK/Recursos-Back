<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';

    // CAMPOS QUE SE PUEDEN LLENAR MASIVAMENTE
    protected $fillable = [
        'user_id',
        'postulante_id',
        'puesto_id',
        'dni',
        'nombres',
        'apellidos',
        'fecha_ingreso',
        'jefe_id',
        'estado',
    ];

    public function puesto() {
        return $this->belongsTo(Puesto::class);
    }

    public function jefe() {
        return $this->belongsTo(Empleado::class, 'jefe_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function vacaciones()
    {
        return $this->hasMany(PeriodoVacacional::class);
    }
}
