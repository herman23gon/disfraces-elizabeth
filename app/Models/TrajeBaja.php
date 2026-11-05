<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrajeBaja extends Model
{
    use HasFactory;

    protected $table = 'traje_bajas';

    public const MOTIVOS = ['Dañado', 'Perdido', 'Desgaste', 'Vendido', 'Otro'];

    protected $fillable = [
        'traje_id',
        'user_id',
        'cantidad',
        'motivo',
        'observacion',
    ];

    public function traje()
    {
        return $this->belongsTo(Traje::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
