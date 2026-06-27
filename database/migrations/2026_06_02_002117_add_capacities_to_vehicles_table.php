<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'fuel_capacity')) {
                $table->decimal('fuel_capacity', 8, 2)->nullable()->after('load_capacity');
            }
            if (!Schema::hasColumn('vehicles', 'compaction_capacity')) {
                $table->decimal('compaction_capacity', 8, 2)->nullable()->after('fuel_capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['fuel_capacity', 'compaction_capacity']);
        });
    }
};