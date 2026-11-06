<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_record', function (Blueprint $table) {
            $table->decimal('estimated_damage', 15, 2)->nullable()->after('fatalities');
        });
    }

    public function down(): void
    {
        Schema::table('incident_record', function (Blueprint $table) {
            $table->dropColumn('estimated_damage');
        });
    }
};
