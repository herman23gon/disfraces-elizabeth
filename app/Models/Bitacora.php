<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacoras';

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'ip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFechaHoraFormateadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s'); // convierte a dd/mm/yyyy hh:mm:ss
    }
}
