<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Postulante; 
use App\Models\PreSeleccion;

class GrupoCapacitacion extends Model
{
    protected $table = 'grupos_capacitacion';
    protected $fillable = ['nombre_grupo', 'area_general', 'tipo', 'fecha_capacitacion', 'hora_capacitacion', 'estado'];

    // Una sola vez la relación de postulantes
    public function postulantes(): HasMany
    {
        return $this->hasMany(Postulante::class, 'grupo_id');
    }

    // Relación de preselecciones
    public function preSelecciones(): HasMany
    {
        return $this->hasMany(PreSeleccion::class, 'grupo_id');
    }
}