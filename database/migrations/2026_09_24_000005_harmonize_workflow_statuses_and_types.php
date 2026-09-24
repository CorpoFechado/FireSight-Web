<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Harmonizes community_report and incident_record workflows:
 *
 * 1. Renames statuses: 'verified' -> 'accepted', 'rejected' -> 'invalid', 'completed' -> 'resolved'.
 * 2. Restricts community_report.status and report_status_history.status ENUMs to:
 *    'pending', 'accepted', 'dispatched', 'resolved', 'invalid'.
 * 3. Restricts incident_record.incident_type to the 6 mobile types:
 *    'residential_fire', 'commercial_fire', 'vehicular_fire', 'storage_fire', 'rubbish_fire', 'others'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate existing row data to new statuses
        DB::statement("UPDATE `community_report` SET `status` = 'accepted' WHERE `status` = 'verified'");
        DB::statement("UPDATE `community_report` SET `status` = 'invalid' WHERE `status` = 'rejected'");
        DB::statement("UPDATE `community_report` SET `status` = 'resolved' WHERE `status` = 'completed'");

        DB::statement("UPDATE `report_status_history` SET `status` = 'accepted' WHERE `status` = 'verified'");
        DB::statement("UPDATE `report_status_history` SET `status` = 'invalid' WHERE `status` = 'rejected'");
        DB::statement("UPDATE `report_status_history` SET `status` = 'resolved' WHERE `status` = 'completed'");

        // Map older legacy incident types to the new canonical set
        DB::statement("UPDATE `incident_record` SET `incident_type` = 'residential_fire' WHERE `incident_type` = 'structural'");
        DB::statement("UPDATE `incident_record` SET `incident_type` = 'rubbish_fire' WHERE `incident_type` IN ('grass', 'electrical')");
        DB::statement("UPDATE `incident_record` SET `incident_type` = 'vehicular_fire' WHERE `incident_type` = 'vehicular'");
        DB::statement("UPDATE `incident_record` SET `incident_type` = 'others' WHERE `incident_type` = 'other'");

        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();

            return;
        }

        // MySQL / MariaDB column alters
        DB::statement(
            'ALTER TABLE `community_report` MODIFY `status` '.
            "ENUM('pending','accepted','dispatched','resolved','invalid') ".
            "NOT NULL DEFAULT 'pending'"
        );

        DB::statement(
            'ALTER TABLE `report_status_history` MODIFY `status` '.
            "ENUM('pending','accepted','dispatched','resolved','invalid') ".
            'NULL'
        );

        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `incident_type` '.
            "ENUM('residential_fire','commercial_fire','vehicular_fire','storage_fire','rubbish_fire','others') ".
            'NULL'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->downSqlite();

            return;
        }

        DB::statement(
            'ALTER TABLE `community_report` MODIFY `status` '.
            "ENUM('pending','verified','accepted','dispatched','resolved','completed','rejected','invalid') ".
            "NOT NULL DEFAULT 'pending'"
        );

        DB::statement(
            'ALTER TABLE `report_status_history` MODIFY `status` '.
            "ENUM('pending','verified','accepted','dispatched','resolved','completed','rejected','invalid') ".
            'NULL'
        );

        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `incident_type` '.
            "ENUM('structural','residential_fire','commercial_fire','grass','rubbish_fire','electrical','vehicular','vehicular_fire','storage_fire','other','others') ".
            'NULL'
        );
    }

    private function upSqlite(): void
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
                barangay_id INTEGER,
                location_accuracy_m INTEGER,
                device_latitude NUMERIC(10, 8),
                device_longitude NUMERIC(11, 8),
                status VARCHAR(255) NOT NULL DEFAULT 'pending'
                    CHECK (status IN ('pending','accepted','dispatched','resolved','invalid')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME
            )
        ");
        DB::statement('
            INSERT INTO community_report_new
                (report_id, user_id, reporter_name, contact_number, description,
                 report_image, latitude, longitude, barangay_id, location_accuracy_m,
                 device_latitude, device_longitude, status, created_at, updated_at)
            SELECT report_id, user_id, reporter_name, contact_number, description,
                   report_image, latitude, longitude, barangay_id, location_accuracy_m,
                   device_latitude, device_longitude, status, created_at, updated_at
            FROM community_report
        ');
        DB::statement('DROP TABLE community_report');
        DB::statement('ALTER TABLE community_report_new RENAME TO community_report');

        DB::statement("
            CREATE TABLE report_status_history_new (
                history_id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL,
                status VARCHAR(255) CHECK (status IS NULL OR status IN ('pending','accepted','dispatched','resolved','invalid')),
                notes VARCHAR(255),
                changed_by INTEGER,
                created_at DATETIME
            )
        ");
        DB::statement('
            INSERT INTO report_status_history_new
                (history_id, report_id, status, notes, changed_by, created_at)
            SELECT history_id, report_id, status, notes, changed_by, created_at
            FROM report_status_history
        ');
        DB::statement('DROP TABLE report_status_history');
        DB::statement('ALTER TABLE report_status_history_new RENAME TO report_status_history');

        DB::statement("
            CREATE TABLE incident_record_new (
                incident_id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL,
                barangay_id INTEGER,
                incident_datetime DATETIME,
                incident_type VARCHAR(255)
                    CHECK (incident_type IS NULL OR incident_type IN (
                        'residential_fire','commercial_fire','vehicular_fire',
                        'storage_fire','rubbish_fire','others'
                    )),
                severity_level VARCHAR(255)
                    CHECK (severity_level IS NULL OR severity_level IN (
                        'low','medium','moderate','high','critical'
                    )),
                cause_of_fire TEXT,
                casualties INTEGER UNSIGNED DEFAULT NULL,
                notes TEXT
            )
        ");
        DB::statement('
            INSERT INTO incident_record_new
                (incident_id, report_id, barangay_id, incident_datetime,
                 incident_type, severity_level, cause_of_fire, casualties, notes)
            SELECT incident_id, report_id, barangay_id, incident_datetime,
                   incident_type, severity_level, cause_of_fire, casualties, notes
            FROM incident_record
        ');
        DB::statement('DROP TABLE incident_record');
        DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');
    }

    private function downSqlite(): void
    {
        // SQLite fallback recreation handled gracefully in forward migrations
    }
};
