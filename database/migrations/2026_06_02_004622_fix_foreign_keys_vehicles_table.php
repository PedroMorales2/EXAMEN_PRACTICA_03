<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Eliminar FKs incorrectas
            $table->dropForeign('vehicles_type_id_foreign');
            $table->dropForeign('vehicles_color_id_foreign');

            // Agregar FKs correctas
            $table->foreign('type_id')
                  ->references('id')
                  ->on('vehicle_types')
                  ->nullOnDelete();

            $table->foreign('color_id')
                  ->references('id')
                  ->on('vehicle_colors')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign('vehicles_type_id_foreign');
            $table->dropForeign('vehicles_color_id_foreign');

            $table->foreign('type_id')
                  ->references('id')
                  ->on('vehicletypes')
                  ->nullOnDelete();

            $table->foreign('color_id')
                  ->references('id')
                  ->on('vehiclecolors')
                  ->nullOnDelete();
        });
    }
};