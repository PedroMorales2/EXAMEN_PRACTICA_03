<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_id')
                ->constrained('maintenances')
                ->cascadeOnDelete();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();

            // Responsable: usuario del proyecto
            $table->foreignId('responsible_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('type', ['PREVENTIVO', 'LIMPIEZA', 'REPARACION']);

            // Día de la semana en formato ISO (1=Lunes ... 7=Domingo)
            $table->unsignedTinyInteger('weekday');

            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
