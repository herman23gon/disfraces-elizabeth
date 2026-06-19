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
        'descripcion',
        'cantidad_disponible',
        'precio_referencia',
        'estado',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}