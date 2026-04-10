<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoVacacional extends Model
{
    protected $table = 'periodos_vacacionales';

    protected $fillable = [
        'empleado_id',
        'fecha_inicio',
        'fecha_fin',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}