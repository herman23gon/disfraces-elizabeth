<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrajeReparacion extends Model
{
    use HasFactory;

    protected $table = 'traje_reparaciones';

    protected $fillable = [
        'traje_id',
        'detalle_alquiler_id',
        'user_id',
        'cantidad',
        'motivo',
        'estado',
        'resuelto_por',
        'fecha_resolucion',
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
    ];

    public function traje()
    {
        return $this->belongsTo(Traje::class);
    }

    public function detalle()
    {
        return $this->belongsTo(DetalleAlquiler::class, 'detalle_alquiler_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolvio()
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'en_reparacion');
    }
}
