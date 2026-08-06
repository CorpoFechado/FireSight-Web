<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Approximate centroid coordinates for each barangay, used to plot
     * markers on the Leaflet map. Not in the original schema — see
     * BarangaySeeder for the important caveat about their precision.
     */
    public function up(): void
    {
        Schema::table('barangay', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('barangay_name');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('barangay', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
