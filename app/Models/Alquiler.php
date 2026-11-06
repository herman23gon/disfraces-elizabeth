<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'alquileres';

    /**
     * Estados en los que todavía hay trajes fuera de la tienda.
     * "parcial": un grupo que ya devolvió algunos trajes, pero no todos.
     */
    public const PENDIENTES = ['activo', 'parcial'];

    protected $fillable = [
    'numero_recibo',
    'tipo',
    'cliente_id',
    'usuario_id',
    'nombre_usuario_traje',
    'estado_traje_entrega',
    'colegio_direccion',
    'curso',
    'turno',
    'fecha_alquiler',
    'fecha_devolucion_programada',
    'total',
    'forma_pago',
    'estado',
];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleAlquiler::class);
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }

    /**
     * Garantías del grupo / del alquiler completo (no de una persona)
     */
    public function garantiasGenerales()
    {
        return $this->hasMany(Garantia::class)->whereNull('detalle_alquiler_id');
    }

    /**
     * Un alquiler grupal puede devolverse en varias partes
     */
    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class);
    }

    /**
     * La última devolución registrada
     */
    public function devolucion()
    {
        return $this->hasOne(Devolucion::class)->latestOfMany();
    }

    /**
     * Alquileres con trajes todavía fuera de la tienda (activos o con devolución parcial)
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', self::PENDIENTES);
    }

    public function scopeVencidos($query)
    {
        return $query->pendientes()->whereDate('fecha_devolucion_programada', '<', today());
    }

    public function estaPendiente(): bool
    {
        return in_array($this->estado, self::PENDIENTES);
    }

    public function estaVencido(): bool
    {
        return $this->estaPendiente() && \Carbon\Carbon::parse($this->fecha_devolucion_programada)->lt(today());
    }

    public function esGrupal(): bool
    {
        return $this->tipo === 'grupal';
    }

    public function getFechaAlquilerFormateadaAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_alquiler)->format('d/m/Y'); // convierte a dd/mm/yyyy
    }

    public function getFechaDevolucionProgramadaFormateadaAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_devolucion_programada)->format('d/m/Y'); // convierte a dd/mm/yyyy
    }
}
