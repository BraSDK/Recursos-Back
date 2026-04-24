<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoCapacitacion extends Model
{
    protected $table = 'grupos_capacitacion';
    protected $fillable = ['nombre_grupo', 'area_general', 'fecha_capacitacion', 'hora_capacitacion', 'estado'];

    public function postulantes(): HasMany
    {
        return $this->hasMany(Postulante::class, 'grupo_id');
    }
}