<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumns('barangay', ['centroid_lat', 'latitude'])) {
            DB::table('barangay')->update([
                'centroid_lat' => DB::raw('COALESCE(centroid_lat, latitude)'),
            ]);
        }

        if (Schema::hasColumns('barangay', ['centroid_lng', 'longitude'])) {
            DB::table('barangay')->update([
                'centroid_lng' => DB::raw('COALESCE(centroid_lng, longitude)'),
            ]);
        }

        if (Schema::hasColumn('barangay', 'boundary')
            && ! Schema::hasColumn('barangay', 'boundary_geojson')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->renameColumn('boundary', 'boundary_geojson');
            });
        }

        if (Schema::hasColumn('barangay', 'latitude')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->dropColumn('latitude');
            });
        }

        if (Schema::hasColumn('barangay', 'longitude')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->dropColumn('longitude');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('barangay', 'latitude')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('barangay_name');
            });
        }

        if (! Schema::hasColumn('barangay', 'longitude')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }

        if (Schema::hasColumns('barangay', ['latitude', 'centroid_lat'])) {
            DB::table('barangay')->update([
                'latitude' => DB::raw('centroid_lat'),
            ]);
        }

        if (Schema::hasColumns('barangay', ['longitude', 'centroid_lng'])) {
            DB::table('barangay')->update([
                'longitude' => DB::raw('centroid_lng'),
            ]);
        }

        if (Schema::hasColumn('barangay', 'boundary_geojson')
            && ! Schema::hasColumn('barangay', 'boundary')) {
            Schema::table('barangay', function (Blueprint $table) {
                $table->renameColumn('boundary_geojson', 'boundary');
            });
        }
    }
};
