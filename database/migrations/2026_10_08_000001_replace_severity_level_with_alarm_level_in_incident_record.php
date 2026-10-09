<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replaces `severity_level` with standalone `alarm_level` on `incident_record`:
 *
 * 1. Adds `alarm_level` enum matching the 13 levels from `afor_report`.
 * 2. Migrates existing data:
 *    low -> 1st_alarm
 *    moderate / medium -> 2nd_alarm
 *    high -> 3rd_alarm
 *    critical -> 5th_alarm
 * 3. Drops `severity_level`.
 *
 * Supports MySQL / MariaDB and SQLite (table rebuild with CHECK constraints).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();

            return;
        }

        DB::statement(
            'ALTER TABLE `incident_record` ADD COLUMN `alarm_level` '.
            "ENUM('1st_alarm','2nd_alarm','3rd_alarm','4th_alarm','5th_alarm',".
            "'task_force_alpha','task_force_bravo','task_force_charlie',".
            "'task_force_delta','task_force_echo','task_force_hotel',".
            "'task_force_india','general_alarm') NULL AFTER `incident_type`"
        );

        DB::statement("UPDATE `incident_record` SET `alarm_level` = '1st_alarm' WHERE `severity_level` = 'low'");
        DB::statement("UPDATE `incident_record` SET `alarm_level` = '2nd_alarm' WHERE `severity_level` IN ('moderate', 'medium')");
        DB::statement("UPDATE `incident_record` SET `alarm_level` = '3rd_alarm' WHERE `severity_level` = 'high'");
        DB::statement("UPDATE `incident_record` SET `alarm_level` = '5th_alarm' WHERE `severity_level` = 'critical'");

        DB::statement('ALTER TABLE `incident_record` DROP COLUMN `severity_level`');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->downSqlite();

            return;
        }

        DB::statement(
            'ALTER TABLE `incident_record` ADD COLUMN `severity_level` '.
            "ENUM('low','medium','moderate','high','critical') NULL AFTER `incident_type`"
        );

        DB::statement("UPDATE `incident_record` SET `severity_level` = 'low' WHERE `alarm_level` = '1st_alarm'");
        DB::statement("UPDATE `incident_record` SET `severity_level` = 'moderate' WHERE `alarm_level` = '2nd_alarm'");
        DB::statement("UPDATE `incident_record` SET `severity_level` = 'high' WHERE `alarm_level` = '3rd_alarm'");
        DB::statement(
            "UPDATE `incident_record` SET `severity_level` = 'critical' ".
            "WHERE `alarm_level` IN ('4th_alarm','5th_alarm','task_force_alpha','task_force_bravo',".
            "'task_force_charlie','task_force_delta','task_force_echo','task_force_hotel','task_force_india','general_alarm')"
        );

        DB::statement('ALTER TABLE `incident_record` DROP COLUMN `alarm_level`');
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
                        'residential_fire','commercial_fire','vehicular_fire',
                        'storage_fire','rubbish_fire','others'
                    )),
                alarm_level VARCHAR(255)
                    CHECK (alarm_level IS NULL OR alarm_level IN (
                        '1st_alarm','2nd_alarm','3rd_alarm','4th_alarm','5th_alarm',
                        'task_force_alpha','task_force_bravo','task_force_charlie',
                        'task_force_delta','task_force_echo','task_force_hotel',
                        'task_force_india','general_alarm'
                    )),
                cause_of_fire TEXT,
                casualties INTEGER UNSIGNED DEFAULT NULL,
                notes TEXT
            )
        ");

        DB::statement("
            INSERT INTO incident_record_new
                (incident_id, report_id, barangay_id, incident_datetime,
                 incident_type, alarm_level, cause_of_fire, casualties, notes)
            SELECT incident_id, report_id, barangay_id, incident_datetime,
                   incident_type,
                   CASE
                       WHEN severity_level = 'low' THEN '1st_alarm'
                       WHEN severity_level IN ('moderate', 'medium') THEN '2nd_alarm'
                       WHEN severity_level = 'high' THEN '3rd_alarm'
                       WHEN severity_level = 'critical' THEN '5th_alarm'
                       ELSE NULL
                   END,
                   cause_of_fire, casualties, notes
            FROM incident_record
        ");

        DB::statement('DROP TABLE incident_record');
        DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_incident_barangay ON incident_record (barangay_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_incident_datetime ON incident_record (incident_datetime)');
    }

    private function downSqlite(): void
    {
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

        DB::statement("
            INSERT INTO incident_record_new
                (incident_id, report_id, barangay_id, incident_datetime,
                 incident_type, severity_level, cause_of_fire, casualties, notes)
            SELECT incident_id, report_id, barangay_id, incident_datetime,
                   incident_type,
                   CASE
                       WHEN alarm_level = '1st_alarm' THEN 'low'
                       WHEN alarm_level = '2nd_alarm' THEN 'moderate'
                       WHEN alarm_level = '3rd_alarm' THEN 'high'
                       WHEN alarm_level IN (
                           '4th_alarm','5th_alarm','task_force_alpha','task_force_bravo',
                           'task_force_charlie','task_force_delta','task_force_echo',
                           'task_force_hotel','task_force_india','general_alarm'
                       ) THEN 'critical'
                       ELSE NULL
                   END,
                   cause_of_fire, casualties, notes
            FROM incident_record
        ");

        DB::statement('DROP TABLE incident_record');
        DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_incident_barangay ON incident_record (barangay_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_incident_datetime ON incident_record (incident_datetime)');
    }
};
