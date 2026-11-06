<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alquiler grupal (una persona por traje, garantías por persona o del grupo)
     * y devolución por partes (cada traje se devuelve por separado).
     */
    public function up(): void
    {
        Schema::table('alquileres', function (Blueprint $table) {
            // individual | grupal
            $table->string('tipo')->default('individual')->after('numero_recibo');
        });

        Schema::table('detalle_alquiler', function (Blueprint $table) {
            // Quién usa este traje (en un curso, cada alumno)
            $table->string('persona')->nullable()->after('traje_id');
            // Piezas que se llevó, separadas por coma (ej: casco,chulo,camisa)
            $table->text('piezas_entregadas')->nullable()->after('cantidad_piezas');
            // Se llena cuando este traje se devuelve (null = pendiente)
            $table->foreignId('devolucion_id')->nullable()->after('subtotal')->constrained('devoluciones')->nullOnDelete();
            $table->string('piezas_faltantes')->nullable()->after('devolucion_id');
            $table->decimal('penalidad', 8, 2)->default(0)->after('piezas_faltantes');
        });

        Schema::table('garantias', function (Blueprint $table) {
            // Garantía de una persona (traje) o, si es null, del grupo / alquiler completo
            $table->foreignId('detalle_alquiler_id')->nullable()->after('alquiler_id')->constrained('detalle_alquiler')->nullOnDelete();
            $table->foreignId('devolucion_id')->nullable()->after('estado')->constrained('devoluciones')->nullOnDelete();
        });

        // Datos existentes: la persona de cada traje es la que figuraba en el alquiler
        DB::statement("
            UPDATE detalle_alquiler d
            JOIN alquileres a ON a.id = d.alquiler_id
            SET d.persona = a.nombre_usuario_traje
        ");

        // Alquileres ya devueltos: sus trajes y garantías quedan ligados a esa devolución
        DB::statement("
            UPDATE detalle_alquiler d
            JOIN devoluciones v ON v.alquiler_id = d.alquiler_id
            SET d.devolucion_id = v.id
        ");
        DB::statement("
            UPDATE garantias g
            JOIN devoluciones v ON v.alquiler_id = g.alquiler_id
            SET g.devolucion_id = v.id
            WHERE g.estado = 'devuelta'
        ");
    }

    public function down(): void
    {
        Schema::table('garantias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('devolucion_id');
            $table->dropConstrainedForeignId('detalle_alquiler_id');
        });

        Schema::table('detalle_alquiler', function (Blueprint $table) {
            $table->dropConstrainedForeignId('devolucion_id');
            $table->dropColumn(['persona', 'piezas_entregadas', 'piezas_faltantes', 'penalidad']);
        });

        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
