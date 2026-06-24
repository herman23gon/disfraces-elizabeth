<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('detalle_alquiler', function (Blueprint $table) {
        $table->id();
        $table->foreignId('alquiler_id')->constrained('alquileres')->onDelete('cascade');
        $table->foreignId('traje_id')->constrained('trajes')->onDelete('restrict');
        $table->integer('cantidad');
        $table->integer('cantidad_piezas');
        $table->string('estado_entrega')->nullable();
        $table->decimal('precio_unitario', 8, 2);
        $table->decimal('subtotal', 8, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_alquiler');
    }
};
