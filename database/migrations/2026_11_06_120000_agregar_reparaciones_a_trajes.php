<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unidades de un traje que volvieron dañadas y no se pueden alquilar hasta repararlas.
     * Disponible = total - alquilados - en reparación
     */
    public function up(): void
    {
        Schema::table('trajes', function (Blueprint $table) {
            $table->integer('cantidad_reparacion')->default(0)->after('cantidad_disponible');
        });

        Schema::create('traje_reparaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traje_id')->constrained('trajes')->cascadeOnDelete();
            // Devolución en la que llegó dañado (puede ser null si se envía desde Trajes)
            $table->foreignId('detalle_alquiler_id')->nullable()->constrained('detalle_alquiler')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('motivo');
            // en_reparacion | reparado | baja
            $table->string('estado')->default('en_reparacion');
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traje_reparaciones');

        Schema::table('trajes', function (Blueprint $table) {
            $table->dropColumn('cantidad_reparacion');
        });
    }
};
