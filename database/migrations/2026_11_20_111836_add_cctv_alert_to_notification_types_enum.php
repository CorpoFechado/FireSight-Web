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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('incident_alert', 'status_update', 'cctv_alert') NOT NULL DEFAULT 'status_update'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notification MODIFY COLUMN notification_type ENUM('incident_alert', 'status_update') NOT NULL DEFAULT 'status_update'");
        }
    }
};
