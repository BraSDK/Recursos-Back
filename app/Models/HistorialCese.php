<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialCese extends Model
{
    protected $table = 'historial_ceses';

    protected $fillable = [
        'empleado_id',
        'motivo_cese',
        'observaciones',
        'recontratable',
        'fecha_salida'
    ];

    protected $casts = [
        'recontratable' => 'boolean',
        'fecha_salida' => 'date'
    ];

    /**
     * Relación con el Empleado que fue cesado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
