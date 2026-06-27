<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicleimages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image', 255);
            $table->integer('profile')->default(0);
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicleimages');
    }
};