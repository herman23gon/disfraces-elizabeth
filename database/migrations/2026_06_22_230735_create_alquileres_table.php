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
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->string('nombre_usuario_traje')->nullable();
            $table->string('estado_traje_entrega')->nullable();
            $table->string('colegio_direccion')->nullable();
            $table->string('curso')->nullable();
            $table->string('turno')->nullable();
            $table->date('fecha_alquiler');
            $table->date('fecha_devolucion_programada');
            $table->decimal('total', 8, 2);
            $table->string('estado')->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alquileres');
    }
};
