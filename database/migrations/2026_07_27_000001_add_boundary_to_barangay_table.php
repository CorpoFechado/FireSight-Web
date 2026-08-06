<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores each barangay's polygon boundary (exterior ring only) as JSON,
     * sourced from lian-per-barangay.geojson. Coordinates are stored in
     * GeoJSON order: [longitude, latitude] per point — the opposite order
     * of the existing `latitude`/`longitude` columns, so don't mix them up
     * when consuming this in PHP or JS.
     */
    public function up(): void
    {
        Schema::table('barangay', function (Blueprint $table) {
            $table->json('boundary')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('barangay', function (Blueprint $table) {
            $table->dropColumn('boundary');
        });
    }
};
