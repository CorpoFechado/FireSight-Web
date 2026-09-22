<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moves the "assessment details" step of the incident workflow from
 * Verify (pending -> verified) to a new Complete step (resolved ->
 * completed), per BFP Lian's process: the fire's cause, severity,
 * incident type, casualties, and notes are only known once the fire is
 * out, not while it's still pending verification.
 *
 * Barangay assignment stays at Verify (needed for dispatch routing and
 * the live map while the incident is still active).
 *
 * Raw SQL is used here (rather than Schema::table(...)->change()) because
 * doctrine/dbal isn't installed, which Laravel's fluent column-change API
 * requires for altering enum/nullable columns.
 *
 * The MySQL-specific MODIFY statements are skipped on SQLite (used in tests)
 * because SQLite stores every column as TEXT and already accepts any value —
 * the enum constraint is enforced at the application layer, not the DB layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we recreate the tables
            // to update the CHECK constraints.

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
                        CHECK (status IN ('pending','verified','rejected','dispatched','resolved','completed')),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            DB::statement('INSERT INTO community_report_new SELECT * FROM community_report');
            DB::statement('DROP TABLE community_report');
            DB::statement('ALTER TABLE community_report_new RENAME TO community_report');

            // Make incident_type and severity_level nullable on SQLite
            DB::statement("
                CREATE TABLE incident_record_new (
                    incident_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    report_id INTEGER NOT NULL,
                    barangay_id INTEGER,
                    data_time DATETIME,
                    incident_type VARCHAR(255)
                        CHECK (incident_type IS NULL OR incident_type IN ('structural','grass','electrical','vehicular','other')),
                    severity_level VARCHAR(255)
                        CHECK (severity_level IS NULL OR severity_level IN ('low','moderate','high','critical')),
                    cause_of_fire TEXT,
                    casualties INTEGER UNSIGNED DEFAULT NULL,
                    notes TEXT
                )
            ");
            DB::statement('INSERT INTO incident_record_new SELECT * FROM incident_record');
            DB::statement('DROP TABLE incident_record');
            DB::statement('ALTER TABLE incident_record_new RENAME TO incident_record');

            return;
        }

        DB::statement(
            'ALTER TABLE `community_report` MODIFY `status` '.
            "ENUM('pending','verified','rejected','dispatched','resolved','completed') ".
            "NOT NULL DEFAULT 'pending'"
        );

        // incident_type and severity_level are no longer set at Verify time,
        // so they must be nullable until the Complete step fills them in.
        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `incident_type` '.
            "ENUM('structural','grass','electrical','vehicular','other') NULL"
        );

        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `severity_level` '.
            "ENUM('low','moderate','high','critical') NULL"
        );

        // Drop the 0 default so an un-completed incident shows "not yet
        // assessed" (NULL) rather than a misleading "0 casualties".
        DB::statement(
            'ALTER TABLE `incident_record` MODIFY `casualties` '.
            'INT UNSIGNED NULL DEFAULT NULL'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE `incident_record` MODIFY `casualties` '.
                'INT UNSIGNED NOT NULL DEFAULT 0'
            );

            DB::statement(
                'ALTER TABLE `incident_record` MODIFY `severity_level` '.
                "ENUM('low','moderate','high','critical') NOT NULL"
            );

            DB::statement(
                'ALTER TABLE `incident_record` MODIFY `incident_type` '.
                "ENUM('structural','grass','electrical','vehicular','other') NOT NULL"
            );

            DB::statement(
                'ALTER TABLE `community_report` MODIFY `status` '.
                "ENUM('pending','verified','rejected','dispatched','resolved') ".
                "NOT NULL DEFAULT 'pending'"
            );
        }
    }
};
