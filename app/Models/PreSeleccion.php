<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreSeleccion extends Model
{
    protected $table = 'pre_selecciones';

    protected $fillable = [
        'dni',
        'nombre_completo',
        'puesto_id',
        'estado',
    ];

    public function puesto(): BelongsTo {
        return $this->belongsTo(Puesto::class);
    }
}
