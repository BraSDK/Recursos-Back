<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoSeleccion extends Model
{
    protected $table = 'procesos_seleccions';

    protected $fillable = [
        'postulante_id',
        'etapa',
        'resultado',
        'observaciones',
        'fecha_evaluacion'
    ];

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class);
    }
}
