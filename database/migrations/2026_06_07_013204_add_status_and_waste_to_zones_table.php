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
        Schema::table('zones', function (Blueprint $table) {
            // Creamos las columnas que faltan en la tabla zones
            $table->decimal('average_waste', 10, 2)->nullable()->after('description');
            $table->string('status', 20)->default('ACTIVO')->after('average_waste');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            // Eliminamos las columnas en caso de hacer un rollback
            $table->dropColumn(['average_waste', 'status']);
        });
    }
};