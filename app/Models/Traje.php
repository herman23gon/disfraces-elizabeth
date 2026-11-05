<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traje extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id',
        'nombre',
        'talla',
        'cantidad_piezas',
        'cantidad_total',
        'descripcion',
        'cantidad_disponible',
        'precio_referencia',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleAlquiler::class);
    }

    public function bajas()
    {
        return $this->hasMany(TrajeBaja::class);
    }

    /**
     * Unidades que están alquiladas ahora (en alquileres activos)
     */
    public function cantidadAlquilada(): int
    {
        return (int) $this->detalles()
            ->whereHas('alquiler', fn ($q) => $q->where('estado', 'activo'))
            ->sum('cantidad');
    }

    /**
     * Se puede ofrecer en un alquiler: traje activo, de una categoría activa y con stock
     */
    public function scopeAlquilables($query)
    {
        return $query->where('estado', true)
            ->where('cantidad_disponible', '>', 0)
            ->whereHas('categoria', fn ($q) => $q->where('estado', true));
    }

    public function esAlquilable(): bool
    {
        return $this->estado && $this->categoria && $this->categoria->estado;
    }
}