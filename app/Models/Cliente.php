<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono',
    ];

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class);
    }

    public function getPrimerNombreAttribute(): string
    {
        return mb_convert_case(explode(' ', trim($this->nombre))[0], MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Enlace que abre WhatsApp (Desktop o Web) con el chat del cliente y el mensaje ya escrito.
     * El mensaje no se envía solo: quien atiende lo revisa y lo envía.
     */
    public function whatsappUrl(?string $mensaje = null): string
    {
        $mensaje ??= "Hola {$this->primer_nombre}, le saludamos de " . config('negocio.nombre') . ".";

        return 'https://wa.me/591' . $this->telefono . '?text=' . rawurlencode($mensaje);
    }

    /**
     * Mensaje para recordar la devolución de un alquiler (vencido o por vencer)
     */
    public function mensajeRecordatorio(Alquiler $alquiler): string
    {
        $fecha = $alquiler->fecha_devolucion_programada_formateada;
        $vencido = \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->lt(today());

        return "Hola {$this->primer_nombre}, le saludamos de " . config('negocio.nombre') . ". "
            . ($vencido
                ? "Le recordamos que el alquiler con recibo {$alquiler->numero_recibo} debía devolverse el {$fecha}. Por favor, acérquese a devolverlo a la brevedad. ¡Gracias!"
                : "Le recordamos que la fecha de devolución del alquiler con recibo {$alquiler->numero_recibo} es el {$fecha}. ¡Gracias!");
    }
}