<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    use HasFactory;

    protected $fillable = [
        'alquiler_id',
        'detalle_alquiler_id',
        'tipo_garantia',
        'descripcion',
        'cantidad',
        'monto',
        'estado',
        'devolucion_id',
    ];

    public const TIPOS = ['Carnet', 'Efectivo', 'Pago QR', 'Celular', 'Joya', 'Otro'];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    /**
     * Devolución en la que se entregó al cliente
     */
    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class);
    }

    /**
     * Traje (persona) al que pertenece; null = garantía del grupo
     */
    public function detalle()
    {
        return $this->belongsTo(DetalleAlquiler::class, 'detalle_alquiler_id');
    }

    /**
     * Texto corto: "Carnet", "Efectivo Bs 100", "Joya (anillo de oro)"
     */
    public function getResumenAttribute(): string
    {
        return $this->tipo_garantia
            . ($this->cantidad > 1 ? ' x' . $this->cantidad : '')
            . ($this->monto ? ' Bs ' . number_format($this->monto, 2) : '')
            . ($this->descripcion ? ' (' . $this->descripcion . ')' : '');
    }
}