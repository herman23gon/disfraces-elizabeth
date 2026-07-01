<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrajeSeeder extends Seeder
{
    public function run(): void
    {
        $trajes = [
            ['nombre' => 'Tinku Azul Hombre',   'categoria_id' => 2, 'talla' => 'm', 'cantidad_piezas' => 5, 'descripcion' => 'casco,chulo,camisa,pantalon,abarca', 'cantidad_disponible' => 3, 'precio_referencia' => 80.00, 'estado' => true],
            ['nombre' => 'Tinku Rojo Mujer',     'categoria_id' => 2, 'talla' => 's', 'cantidad_piezas' => 4, 'descripcion' => 'pollera,blusa,sombrero,abarca',       'cantidad_disponible' => 2, 'precio_referencia' => 80.00, 'estado' => true],
            ['nombre' => 'Morenada Hombre',      'categoria_id' => 2, 'talla' => 'l', 'cantidad_piezas' => 6, 'descripcion' => 'mascara,capa,camisa,pantalon,cinturon,bota', 'cantidad_disponible' => 2, 'precio_referencia' => 100.00,'estado' => true],
            ['nombre' => 'Morenada Mujer',       'categoria_id' => 2, 'talla' => 'm', 'cantidad_piezas' => 5, 'descripcion' => 'pollera,blusa,sombrero,cinturon,bota', 'cantidad_disponible' => 2, 'precio_referencia' => 100.00,'estado' => true],
            ['nombre' => 'Caporales Hombre',     'categoria_id' => 2, 'talla' => 'm', 'cantidad_piezas' => 4, 'descripcion' => 'camisa,pantalon,botin,sombrero',       'cantidad_disponible' => 3, 'precio_referencia' => 90.00, 'estado' => true],
            ['nombre' => 'Caporales Mujer',      'categoria_id' => 2, 'talla' => 's', 'cantidad_piezas' => 3, 'descripcion' => 'pollera,blusa,sombrero',               'cantidad_disponible' => 3, 'precio_referencia' => 90.00, 'estado' => true],
            ['nombre' => 'Diablada Hombre',      'categoria_id' => 2, 'talla' => 'l', 'cantidad_piezas' => 7, 'descripcion' => 'mascara,capa,camisa,pantalon,cinturon,guante,bota', 'cantidad_disponible' => 1, 'precio_referencia' => 120.00,'estado' => true],
            ['nombre' => 'Tobas Hombre',         'categoria_id' => 2, 'talla' => 'm', 'cantidad_piezas' => 3, 'descripcion' => 'camisa,pantalon,tocado',               'cantidad_disponible' => 2, 'precio_referencia' => 75.00, 'estado' => true],
            ['nombre' => 'Disfraz Payaso',       'categoria_id' => 3, 'talla' => 'm', 'cantidad_piezas' => 2, 'descripcion' => 'traje,peluca',                         'cantidad_disponible' => 3, 'precio_referencia' => 50.00, 'estado' => true],
            ['nombre' => 'Disfraz Pirata',       'categoria_id' => 3, 'talla' => 'm', 'cantidad_piezas' => 3, 'descripcion' => 'camisa,pantalon,sombrero',             'cantidad_disponible' => 3, 'precio_referencia' => 55.00, 'estado' => true],
            ['nombre' => 'Disfraz Princesa',     'categoria_id' => 3, 'talla' => 's', 'cantidad_piezas' => 3, 'descripcion' => 'vestido,corona,varita',                'cantidad_disponible' => 2, 'precio_referencia' => 65.00, 'estado' => true],
        ];

        foreach ($trajes as $traje) {
            DB::table('trajes')->insert(array_merge($traje, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}