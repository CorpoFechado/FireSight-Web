<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Harmonizes `community_report` for mobile integration:
 *
 * 1. Expands `status` enum to include mobile statuses ('accepted', 'invalid').
 * 2. Adds mobile-originated columns: barangay_id, location_accuracy_m,
 *    device_latitude, device_longitude, updated_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();
        } else {
            $this->upMysql();
        }

        // Add new columns (Schema::table works on all drivers)
        Schema::table('community_report', function (Blueprint $table) {
            if (! Schema::hasColumn('community_report', 'barangay_id')) {
                $table->unsignedBigInteger('barangay_id')->nullable()->after('longitude');
                $table->foreign('barangay_id', 'fk_cr_barangay')
                    ->references('barangay_id')
                    ->on('barangay')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('community_report', 'location_accuracy_m')) {
                $table->integer('location_accuracy_m')->nullable()->after('barangay_id');
            }

            if (! Schema::hasColumn('community_report', 'device_latitude')) {
                $table->decimal('device_latitude', 10, 8)->nullable()->after('location_accuracy_m');
            }

            if (! Schema::hasColumn('community_report', 'device_longitude')) {
                $table->decimal('device_longitude', 11, 8)->nullable()->after('device_latitude');
            }

            if (! Schema::hasColumn('community_report', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_report', function (Blueprint $table) {
            if (Schema::hasColumn('community_report', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('community_report', 'device_longitude')) {
                $table->dropColumn('device_longitude');
            }
            if (Schema::hasColumn('community_report', 'device_latitude')) {
                $table->dropColumn('device_latitude');
            }
            if (Schema::hasColumn('community_report', 'location_accuracy_m')) {
                $table->dropColumn('location_accuracy_m');
            }
            if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('community_report', 'barangay_id')) {
                $table->dropForeign('fk_cr_barangay');
            }
            if (Schema::hasColumn('community_report', 'barangay_id')) {
                $table->dropColumn('barangay_id');
            }
        });

        if (DB::getDriverName() === 'sqlite') {
            $this->downSqlite();
        } else {
            DB::statement(
                'ALTER TABLE `community_report` MODIFY `status` '.
                "ENUM('pending','verified','rejected','dispatched','resolved','completed') ".
                "NOT NULL DEFAULT 'pending'"
            );
        }
    }

    private function upMysql(): void
    {
        DB::statement(
            'ALTER TABLE `community_report` MODIFY `status` '.
            "ENUM('pending','verified','accepted','dispatched','resolved','completed','rejected','invalid') ".
            "NOT NULL DEFAULT 'pending'"
        );
    }

    private function upSqlite(): void
    {
        // SQLite cannot modify columns; recreate with expanded CHECK constraint.
        DB::statement("
            CREATE TABLE community_report_new (
                report_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                reporter_name VARCHAR(150) NOT NULL,
                contact_number VARCHAR(20) NOT NULL,
                description TEXT,
                report_image VARCHAR(255),
                latitude NUMERIC(10, 8) NOT NULL,
                longitude NUMERIC(11, 8) NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT 'pending'
                    CHECK (status IN (
                        'pending','verified','accepted','dispatched',
                        'resolved','completed','rejected','invalid'
                    )),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        DB::statement('INSERT INTO community_report_new SELECT * FROM community_report');
        DB::statement('DROP TABLE community_report');
        DB::statement('ALTER TABLE community_report_new RENAME TO community_report');
    }

    private function downSqlite(): void
    {
        DB::statement("
            CREATE TABLE community_report_new (
                report_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                reporter_name VARCHAR(150) NOT NULL,
                contact_number VARCHAR(20) NOT NULL,
                description TEXT,
                report_image VARCHAR(255),
                latitude NUMERIC(10, 8) NOT NULL,
                longitude NUMERIC(11, 8) NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT 'pending'
                    CHECK (status IN (
                        'pending','verified','rejected','dispatched','resolved','completed'
                    )),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        DB::statement('INSERT INTO community_report_new SELECT * FROM community_report');
        DB::statement('DROP TABLE community_report');
        DB::statement('ALTER TABLE community_report_new RENAME TO community_report');
    }
};
