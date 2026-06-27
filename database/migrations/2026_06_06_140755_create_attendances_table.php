<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->time('time');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->enum('type', ['Entrada', 'Salida']);
            $table->enum('status', ['Presente', 'Ausente'])->default('Presente');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('schedule_id')
                  ->references('id')
                  ->on('schedules')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};