<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brandmodels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('code', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')
                  ->references('id')
                  ->on('brands')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brandmodels');
    }
};