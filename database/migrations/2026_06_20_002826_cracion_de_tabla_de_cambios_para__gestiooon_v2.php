<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacion_cambios', function (Blueprint $table) {
            $table->foreignId('cambio_masivo_id')
                  ->nullable()
                  ->after('motivo')
                  ->constrained('cambios_masivos')
                  ->nullOnDelete();
            $table->boolean('revertido')
                  ->default(false)
                  ->after('cambio_masivo_id');
        });
    }

    public function down(): void
    {
        Schema::table('programacion_cambios', function (Blueprint $table) {
            $table->dropForeign(['cambio_masivo_id']);
            $table->dropColumn(['cambio_masivo_id', 'revertido']);
        });
    }
};