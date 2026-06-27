<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id');
            $table->date('date');
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('conductor_id');
            $table->unsignedBigInteger('ayudante1_id');
            $table->unsignedBigInteger('ayudante2_id')->nullable();
            $table->enum('status', ['Programado', 'Finalizado'])->default('Programado');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('personal_groups')->cascadeOnDelete();
            $table->foreign('zone_id')->references('id')->on('zones')->cascadeOnDelete();
            $table->foreign('schedule_id')->references('id')->on('schedules')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('conductor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('ayudante1_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('ayudante2_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programaciones');
    }
};
