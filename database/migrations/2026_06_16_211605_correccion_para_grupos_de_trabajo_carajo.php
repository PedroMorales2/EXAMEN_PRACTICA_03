<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Desactivar restricciones para poder borrar las tablas sin que MySQL proteste
        Schema::disableForeignKeyConstraints();

        // Drop pivot table first if exists
        Schema::dropIfExists('personal_group_users');
        Schema::dropIfExists('personal_groups');

        Schema::create('personal_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->foreignId('zone_id')->constrained('zones')->restrictOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->json('days'); // ["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"]
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
        });

        // Pivot: members of a group (conductor + N ayudantes)
        Schema::create('personal_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_group_id')->constrained('personal_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('role', ['conductor', 'ayudante']);
            $table->unsignedTinyInteger('order')->default(1); // 1 = ayudante1, 2 = ayudante2, etc.
            $table->timestamps();

            // A user can only appear once per group
            $table->unique(['personal_group_id', 'user_id']);
        });

        // 2. Volver a activar las restricciones una vez creadas las tablas
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // También las desactivas aquí por seguridad al hacer rollback
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('personal_group_users');
        Schema::dropIfExists('personal_groups');

        Schema::enableForeignKeyConstraints();
    }
};