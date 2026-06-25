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
        'cantidad',
        'cantidad_piezas',
        'estado_entrega',
        'precio_unitario',
        'subtotal',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function traje()
    {
        return $this->belongsTo(Traje::class);
    }
}