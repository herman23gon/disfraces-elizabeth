<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devoluciones';

    protected $fillable = [
        'alquiler_id',
        'usuario_id',
        'fecha_devolucion',
        'observaciones',
        'penalidad',
        'forma_pago_penalidad',
        ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}