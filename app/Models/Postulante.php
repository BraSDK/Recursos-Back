<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postulante extends Model
{
    protected $table = 'postulantes';

    protected $fillable = [
        'puesto_id', 'grupo_id','foto_path', 'cv_path', 'apellido_paterno', 'apellido_materno', 'nombres', 
        'dni', 'edad', 'estado_civil', 'ubigeo_dep_prov', 'distrito', 'fecha_nacimiento', 
        'sexo', 'direccion', 'telefono_fijo', 'celular', 'email', 
        'formacion_academica', 'experiencia_laboral', 'tiene_hijos', 'cantidad_hijos', 
        'esta_embarazada', 'emergencia_nombre', 'emergencia_parentesco', 
        'emergencia_telefono', 'enfermedades_alergias', 'motivo_laborar', 
        'horario_interes', 'salario_sugerido', 'estado_proceso', 'es_reingreso', 'comentarios_reclutador'
    ];

    // Esto es CRÍTICO para manejar los datos del formulario móvil
    protected $casts = [
        'formacion_academica' => 'array',
        'experiencia_laboral' => 'array',
        'fecha_nacimiento' => 'date',
        'tiene_hijos' => 'boolean',
        'esta_embarazada' => 'boolean',
        'es_reingreso' => 'boolean',
    ];

    /**
     * Relación con el Puesto al que postula
     */
    // Relación para obtener el nombre del puesto
    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    /**
     * Relación con las etapas de su proceso de selección
     */
    public function procesosSeleccion(): HasMany
    {
        return $this->hasMany(ProcesoSeleccion::class);
    }

    /**
     * Si llega a ser empleado, esta es la conexión
     */
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'postulante_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoCapacitacion::class, 'grupo_id');
    }
}