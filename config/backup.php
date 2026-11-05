<?php

return [

    /*
     * Carpeta adicional donde se copian las copias automáticas (Descargas, un flash, etc.).
     * Se puede cambiar desde la página Copia de Seguridad.
     */
    'carpeta_adicional' => env('BACKUP_CARPETA_ADICIONAL'),

    /*
     * Cantidad de copias automáticas que se conservan en cada carpeta
     */
    'conservar' => (int) env('BACKUP_CONSERVAR', 30),

];
