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
        if (DB::getDriverName() !== 'sqlite') {
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
