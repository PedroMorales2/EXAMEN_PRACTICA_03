<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_groups', function (Blueprint $table) {
            $table->json('work_days')->nullable()->after('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_groups', function (Blueprint $table) {
            // Elimina la columna si se hace un rollback
            $table->dropColumn('work_days'); 
        });
    }
};