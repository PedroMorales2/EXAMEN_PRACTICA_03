<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Apagar restricciones para que MySQL no bloquee los DROPS
        Schema::disableForeignKeyConstraints();

        // 2. Limpiar CUALQUIER rastro de tablas de programaciones anteriores
        Schema::dropIfExists('programacion_cambios');
        Schema::dropIfExists('programacion_ayudantes');
        Schema::dropIfExists('assignments'); // <── Borramos el renombre antiguo
        Schema::dropIfExists('programaciones'); // <── Borramos la original

        // ── Tabla principal ────────────────────────────────────
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_group_id')->constrained('personal_groups')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones');
            $table->foreignId('schedule_id')->constrained('schedules');   // turno
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('conductor_id')->constrained('users');      // puede diferir del grupo
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->json('dias');                                          // ['Lun','Mié','Vie']
            $table->text('observaciones')->nullable();
            $table->string('status', 20)->default('Programado');          // Programado|Finalizado|Cancelado
            $table->timestamps();
        });

        // ── Pivot ayudantes (pueden diferir de los del grupo) ──
        Schema::create('programacion_ayudantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        // ── Historial de cambios ───────────────────────────────
        Schema::create('programacion_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programacion_id')->constrained('programaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');           // quién hizo el cambio
            $table->string('campo', 60);                                  // qué campo cambió
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();
        });

        // 3. Volver a encender las restricciones
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('programacion_cambios');
        Schema::dropIfExists('programacion_ayudantes');
        Schema::dropIfExists('programaciones');

        Schema::enableForeignKeyConstraints();
    }
};