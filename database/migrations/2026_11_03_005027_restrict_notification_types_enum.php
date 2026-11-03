<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert any legacy rows if any existed to status_update before restricting
        DB::table('notification')
            ->whereNotIn('notification_type', ['incident_alert', 'status_update'])
            ->update(['notification_type' => 'status_update']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('incident_alert', 'status_update') NOT NULL DEFAULT 'status_update'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('incident_alert', 'status_update', 'system', 'reminder') NOT NULL DEFAULT 'status_update'");
        }
    }
};
