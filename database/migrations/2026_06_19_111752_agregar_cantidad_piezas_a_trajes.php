<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trajes', function (Blueprint $table) {
            $table->integer('cantidad_piezas')->default(1)->after('talla');
        });
    }

    public function down(): void
    {
        Schema::table('trajes', function (Blueprint $table) {
            $table->dropColumn('cantidad_piezas');
        });
    }
};