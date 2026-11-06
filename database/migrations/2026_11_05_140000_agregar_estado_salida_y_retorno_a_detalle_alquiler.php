<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado de cada traje al entregarlo y al devolverlo (para comparar y justificar penalidades)
     */
    public function up(): void
    {
        Schema::table('detalle_alquiler', function (Blueprint $table) {
            $table->string('estado_salida')->nullable()->after('estado_entrega');   // Nuevo, Sano, Regular, Con detalle
            $table->string('estado_retorno')->nullable()->after('penalidad');       // Sano, Regular, Con detalle, Roto
        });

        // Datos existentes: el estado general del alquiler pasa a cada traje, si es uno de los valores conocidos
        DB::statement("
            UPDATE detalle_alquiler d
            JOIN alquileres a ON a.id = d.alquiler_id
            SET d.estado_salida = CASE LOWER(TRIM(a.estado_traje_entrega))
                WHEN 'nuevo' THEN 'Nuevo'
                WHEN 'sano' THEN 'Sano'
                WHEN 'regular' THEN 'Regular'
                ELSE NULL END
        ");
    }

    public function down(): void
    {
        Schema::table('detalle_alquiler', function (Blueprint $table) {
            $table->dropColumn(['estado_salida', 'estado_retorno']);
        });
    }
};
