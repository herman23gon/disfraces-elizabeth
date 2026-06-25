<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    use HasFactory;

    protected $fillable = [
        'alquiler_id',
        'tipo_garantia',
        'descripcion',
        'cantidad',
        'monto',
        'estado',
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }
}