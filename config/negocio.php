<?php

/*
 * Datos del negocio que aparecen en el sistema: menú, login, página de inicio,
 * recibos, PDFs y mensajes de WhatsApp. Cambiar aquí los actualiza en todos lados.
 * (Más adelante se podrán editar desde una pantalla de "Configuración del negocio".)
 */
return [

    'nombre' => env('NEGOCIO_NOMBRE', 'Disfraces Elizabeth'),

    'eslogan' => env('NEGOCIO_ESLOGAN', 'Alquiler de trajes típicos y disfraces'),

    'direccion' => env('NEGOCIO_DIRECCION', 'Av. El Mechero, 3 cuadras de la Rotonda'),

    'ciudad' => env('NEGOCIO_CIUDAD', 'Santa Cruz de la Sierra - Bolivia'),

];
