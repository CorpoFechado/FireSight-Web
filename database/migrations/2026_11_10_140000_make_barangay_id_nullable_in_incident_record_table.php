<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make barangay_id nullable in incident_record table so that community reports
     * originating outside Lian borders (which have no assigned barangay_id) can be
     * marked as resolved without violating foreign key or NOT NULL constraints.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('incident_record', function (Blueprint $table) {
                try {
                    $table->dropForeign(['barangay_id']);
                } catch (Throwable $e) {
                    // Ignore if foreign key was already dropped or named differently
                }
            });

            DB::statement('ALTER TABLE `incident_record` MODIFY `barangay_id` BIGINT UNSIGNED NULL DEFAULT NULL');

            Schema::table('incident_record', function (Blueprint $table) {
                $table->foreign('barangay_id', 'incident_record_barangay_id_foreign')
                    ->references('barangay_id')
                    ->on('barangay')
                    ->nullOnDelete();
            });

            return;
        }

        Schema::table('incident_record', function (Blueprint $table) {
            $table->unsignedBigInteger('barangay_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('incident_record', function (Blueprint $table) {
                try {
                    $table->dropForeign(['barangay_id']);
                } catch (Throwable $e) {
                    // Ignore if foreign key was already dropped
                }
            });

            DB::statement('ALTER TABLE `incident_record` MODIFY `barangay_id` BIGINT UNSIGNED NOT NULL');

            Schema::table('incident_record', function (Blueprint $table) {
                $table->foreign('barangay_id', 'incident_record_barangay_id_foreign')
                    ->references('barangay_id')
                    ->on('barangay');
            });

            return;
        }

        Schema::table('incident_record', function (Blueprint $table) {
            $table->unsignedBigInteger('barangay_id')->nullable(false)->change();
        });
    }
};
