<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cambios_masivos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_cambio', ['turno', 'conductor', 'ocupante', 'vehiculo']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();

            // IDs de los valores cambiados (schedule_id / user_id / vehicle_id según tipo)
            $table->unsignedBigInteger('valor_anterior_id')->nullable(); // a quién/qué se reemplazó
            $table->unsignedBigInteger('valor_nuevo_id')->nullable();    // por quién/qué se reemplazó

            $table->foreignId('cambio_id')->nullable()->constrained('cambios')->nullOnDelete(); // motivo
            $table->text('descripcion')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quién ejecutó
            $table->unsignedInteger('afectadas')->default(0); // cuántas programaciones modificó

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cambios_masivos');
    }
};