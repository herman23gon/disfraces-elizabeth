<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleAlquiler extends Model
{
    use HasFactory;

    protected $table = 'detalle_alquiler';

    protected $fillable = [
        'alquiler_id',
        'traje_id',
        'persona',
        'cantidad',
        'cantidad_piezas',
        'piezas_entregadas',
        'estado_entrega',
        'estado_salida',
        'precio_unitario',
        'subtotal',
        'devolucion_id',
        'piezas_faltantes',
        'penalidad',
        'estado_retorno',
    ];

    public const ESTADOS_SALIDA = ['Nuevo', 'Sano', 'Regular', 'Con detalle'];
    public const ESTADOS_RETORNO = ['Sano', 'Regular', 'Con detalle', 'Roto'];

    /**
     * Nivel de desgaste de cada estado, para saber si el traje volvió peor de lo que salió
     */
    public const NIVEL_ESTADO = ['Nuevo' => 0, 'Sano' => 0, 'Regular' => 1, 'Con detalle' => 2, 'Roto' => 3];

    /**
     * true si volvió en peor estado que el que salió (ej: salió Sano, volvió Con detalle)
     */
    public function volvioPeor(): bool
    {
        if (!$this->estado_retorno) {
            return false;
        }

        return (self::NIVEL_ESTADO[$this->estado_retorno] ?? 0) > (self::NIVEL_ESTADO[$this->estado_salida] ?? 0);
    }

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function traje()
    {
        return $this->belongsTo(Traje::class);
    }

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class);
    }

    /**
     * Reparación abierta si este traje volvió dañado y se envió a reparar
     */
    public function reparacion()
    {
        return $this->hasOne(TrajeReparacion::class, 'detalle_alquiler_id');
    }

    /**
     * Garantías que dejó la persona de este traje
     */
    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }

    /**
     * Trajes que todavía no se devolvieron, de alquileres pendientes
     */
    public function scopePendientes($query)
    {
        return $query->whereNull('devolucion_id')
            ->whereHas('alquiler', fn ($q) => $q->pendientes());
    }

    public function estaDevuelto(): bool
    {
        return $this->devolucion_id !== null;
    }

    /**
     * Piezas que se llevó, como lista (ej: ['casco', 'chulo'])
     */
    public function getListaPiezasAttribute(): array
    {
        return collect(explode(',', (string) $this->piezas_entregadas))->map(fn ($p) => trim($p))->filter()->values()->all();
    }
}
