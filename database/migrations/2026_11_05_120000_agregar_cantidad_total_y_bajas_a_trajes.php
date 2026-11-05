<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trajes', function (Blueprint $table) {
            // Unidades que tiene la tienda (disponibles + alquiladas)
            $table->integer('cantidad_total')->default(0)->after('cantidad_piezas');
        });

        // Total actual = lo disponible + lo que está alquilado ahora (alquileres activos)
        DB::statement("
            UPDATE trajes t
            SET t.cantidad_total = t.cantidad_disponible + COALESCE((
                SELECT SUM(d.cantidad)
                FROM detalle_alquiler d
                JOIN alquileres a ON a.id = d.alquiler_id
                WHERE d.traje_id = t.id AND a.estado = 'activo'
            ), 0)
        ");

        // Historial de unidades dadas de baja (dañadas, perdidas, etc.)
        Schema::create('traje_bajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traje_id')->constrained('trajes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('cantidad');
            $table->string('motivo');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traje_bajas');

        Schema::table('trajes', function (Blueprint $table) {
            $table->dropColumn('cantidad_total');
        });
    }
};
