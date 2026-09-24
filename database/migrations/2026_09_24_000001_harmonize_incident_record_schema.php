<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Harmonizes `incident_record` for mobile integration:
 *
 * 1. Renames `data_time` → `incident_datetime` (typo fix + clarity).
 * 2. Expands `incident_type` enum to cover both web and mobile values.
 * 3. Expands `severity_level` enum to cover both web ('moderate') and
 *    mobile ('medium') values.
 *
 * Raw SQL MODIFY/RENAME is used because doctrine/dbal is not installed.
 * SQLite (used in tests) falls back to table recreation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();

            return;
        }

        // 1. Rename + set type in one CHANGE COLUMN (MariaDB 10.4 compatible)
        DB::statement(
            'ALTER TABLE `incident_record` CHANGE COLUMN `data_time` `incident_datetime` DATETIME NULL DEFAULT NULL'
        );

        // 2. Expand incident_type
        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `incident_type` '.
            "ENUM('structural','residential_fire','commercial_fire','grass','rubbish_fire','electrical','vehicular','vehicular_fire','storage_fire','other','others') ".
            "NULL DEFAULT 'structural'"
        );

        // 3. Expand severity_level
        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `severity_level` '.
            "ENUM('low','medium','moderate','high','critical') ".
            "NULL DEFAULT 'low'"
        );

        // 4. Re-create the datetime index under the new column name
        DB::statement('ALTER TABLE `incident_record` DROP INDEX IF EXISTS `idx_incident_datetime`');
        DB::statement('ALTER TABLE `incident_record` ADD INDEX `idx_incident_datetime` (`incident_datetime`)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->downSqlite();

            return;
        }

        DB::statement('ALTER TABLE `incident_record` DROP INDEX IF EXISTS `idx_incident_datetime`');

        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `severity_level` '.
            "ENUM('low','moderate','high','critical') NULL"
        );

        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `incident_type` '.
            "ENUM('structural','grass','electrical','vehicular','other') NULL"
        );

        // Rename back using CHANGE COLUMN (MariaDB 10.4 compatible)
        DB::statement(
            'ALTER TABLE `incident_record` CHANGE COLUMN `incident_datetime` `data_time` DATETIME NULL DEFAULT NULL'
        );

        DB::statement('ALTER TABLE `incident_record` ADD INDEX `idx_incident_datetime` (`data_time`)');
    }

    private function upSqlite(): void
    {
        DB::statement("
            CREATE TABLE incident_record_new (
                incident_id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL,
                barangay_id INTEGER,
                incident_datetime DATETIME,
                incident_type VARCHAR(255)
                    CHECK (incident_type IS NULL OR incident_type IN (
                        'structural','residential_fire','commercial_fire','grass',
                        'rubbish_fire','electrical','vehicular','vehicular_fire',
                        'storage_fire','other','others'
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
            SELECT incident_id, report_id, barangay_id, data_time,
                   incident_type, severity_level, cause_of_fire, casualties, notes
            FROM incident_record
        ');

        DB::statement('DROP TABLE incident_record');
        DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');
    }

    private function downSqlite(): void
    {
        DB::statement("
            CREATE TABLE incident_record_new (
                incident_id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL,
                barangay_id INTEGER,
                data_time DATETIME,
                incident_type VARCHAR(255)
                    CHECK (incident_type IS NULL OR incident_type IN (
                        'structural','grass','electrical','vehicular','other'
                    )),
                severity_level VARCHAR(255)
                    CHECK (severity_level IS NULL OR severity_level IN (
                        'low','moderate','high','critical'
                    )),
                cause_of_fire TEXT,
                casualties INTEGER UNSIGNED DEFAULT NULL,
                notes TEXT
            )
        ");

        DB::statement('
            INSERT INTO incident_record_new
                (incident_id, report_id, barangay_id, data_time,
                 incident_type, severity_level, cause_of_fire, casualties, notes)
            SELECT incident_id, report_id, barangay_id, incident_datetime,
                   incident_type, severity_level, cause_of_fire, casualties, notes
            FROM incident_record
        ');

        DB::statement('DROP TABLE incident_record');
        DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');
    }
};
