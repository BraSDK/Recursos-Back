<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puesto extends Model
{
    protected $table = 'puestos';

    // ESTO ES LO QUE SOLUCIONA EL ERROR 500
    protected $fillable = [
        'departamento_id',
        'nombre_puesto',
        'salario_base'
    ];

    /**
     * Relación con Departamento
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Relación con Postulantes
     */
    public function postulantes(): HasMany
    {
        return $this->hasMany(Postulante::class);
    }
}
