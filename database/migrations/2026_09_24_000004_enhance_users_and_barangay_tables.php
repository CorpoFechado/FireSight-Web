<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhances the `users` and `barangay` tables with mobile-schema fields.
 *
 * users:
 *   - middle_name, suffix — name components used in mobile profile
 *   - google_id           — OAuth identifier for Google Sign-In
 *   - profile_image       — relative path/URL to avatar
 *   - is_verified         — resident account email/phone verification flag
 *
 * barangay:
 *   - centroid_lat / centroid_lng — mobile uses these for map centering;
 *     web already has latitude/longitude (boundary centroid), both are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name', 100)->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('users', 'suffix')) {
                $table->string('suffix', 10)->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id', 255)->nullable()->unique()->after('password');
            }

            if (! Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image', 255)->nullable()->after('google_id');
            }

            if (! Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('profile_image');
            }
        });

        Schema::table('barangay', function (Blueprint $table) {
            if (! Schema::hasColumn('barangay', 'centroid_lat')) {
                $table->decimal('centroid_lat', 10, 8)->nullable()->after('boundary');
            }

            if (! Schema::hasColumn('barangay', 'centroid_lng')) {
                $table->decimal('centroid_lng', 11, 8)->nullable()->after('centroid_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangay', function (Blueprint $table) {
            $table->dropColumnIfExists('centroid_lng');
            $table->dropColumnIfExists('centroid_lat');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('is_verified');
            $table->dropColumnIfExists('profile_image');
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }
            $table->dropColumnIfExists('suffix');
            $table->dropColumnIfExists('middle_name');
        });
    }
};
