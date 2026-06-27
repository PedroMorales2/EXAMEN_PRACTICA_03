<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('plate', 20)->nullable();
            $table->string('year', 4)->nullable();
            $table->integer('occupant_capacity')->nullable();
            $table->integer('load_capacity')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('model_id')->references('id')->on('brandmodels')->nullOnDelete();
            $table->foreign('type_id')->references('id')->on('vehicle_types')->nullOnDelete();
            $table->foreign('color_id')->references('id')->on('vehicle_colors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};