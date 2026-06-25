<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

    protected $table = 'alquileres';

    protected $fillable = [
    'numero_recibo',
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
}