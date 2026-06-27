<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si ya existen datos y quieres conservarlos, deberías migrarlos manualmente
        // antes de correr esto (expandiendo cada rango en filas por fecha).
        // Como mencionaste que estás en desarrollo, recreamos limpio.

        Schema::dropIfExists('programacion_cambios');
        Schema::dropIfExists('programacion_ayudantes'); // pivot ayudantes, si existe con ese nombre
        Schema::dropIfExists('programaciones');

        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->nullable()->index(); // agrupa filas creadas juntas (mismo formulario)
            $table->foreignId('personal_group_id')->nullable()->constrained('personal_groups')->nullOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->restrictOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('conductor_id')->constrained('users')->restrictOnDelete();
            $table->date('fecha'); // ── un único día concreto por fila ──
            $table->text('observaciones')->nullable();
            $table->enum('status', ['Programado', 'Finalizado', 'Cancelado', 'Reprogramado'])->default('Programado');
            $table->timestamps();

            // Un usuario no puede tener 2 filas de conductor el mismo día (a nivel de constraint suave;
            // la validación real de "mismo usuario en 2 roles distintos el mismo día" se hace en PHP)
            $table->index(['fecha', 'conductor_id']);
            $table->index(['personal_group_id', 'fecha']);
        });

        // Pivot de ayudantes por programación (fila = un día concreto)
        Schema::create('programacion_ayudante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['programacion_id', 'user_id']);
            $table->index('user_id');
        });

        // Historial de cambios (igual que antes, pero ahora por fila-fecha)
        Schema::create('programacion_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('campo', 50);
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programacion_cambios');
        Schema::dropIfExists('programacion_ayudante');
        Schema::dropIfExists('programaciones');
    }
};