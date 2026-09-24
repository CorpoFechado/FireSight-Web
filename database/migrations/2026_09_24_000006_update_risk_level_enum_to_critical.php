<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Updates `risk_assessment.risk_level` enum values:
 * Replaces legacy 'severe' with 'critical' so all risk and severity tiers
 * consistently follow: 'low', 'moderate', 'high', 'critical'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->upSqlite();

            return;
        }

        // 1. Temporarily expand enum to include both 'severe' and 'critical'
        DB::statement("ALTER TABLE `risk_assessment` MODIFY `risk_level` ENUM('low', 'moderate', 'high', 'severe', 'critical') NOT NULL");

        // 2. Update existing data
        DB::statement("UPDATE `risk_assessment` SET `risk_level` = 'critical' WHERE `risk_level` = 'severe'");

        // 3. Constrain enum to canonical values
        DB::statement("ALTER TABLE `risk_assessment` MODIFY `risk_level` ENUM('low', 'moderate', 'high', 'critical') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->downSqlite();

            return;
        }

        // 1. Temporarily expand enum
        DB::statement("ALTER TABLE `risk_assessment` MODIFY `risk_level` ENUM('low', 'moderate', 'high', 'critical', 'severe') NOT NULL");

        // 2. Revert data
        DB::statement("UPDATE `risk_assessment` SET `risk_level` = 'severe' WHERE `risk_level` = 'critical'");

        // 3. Revert enum constraint
        DB::statement("ALTER TABLE `risk_assessment` MODIFY `risk_level` ENUM('low', 'moderate', 'high', 'severe') NOT NULL");
    }

    private function upSqlite(): void
    {
        DB::statement("
            CREATE TABLE risk_assessment_new (
                risk_id INTEGER PRIMARY KEY AUTOINCREMENT,
                barangay_id INTEGER NOT NULL,
                date DATE NOT NULL,
                prediction_score NUMERIC NOT NULL,
                risk_level VARCHAR(255) NOT NULL
                    CHECK (risk_level IN ('low', 'moderate', 'high', 'critical')),
                generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (barangay_id) REFERENCES barangay (barangay_id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            INSERT INTO risk_assessment_new (risk_id, barangay_id, date, prediction_score, risk_level, generated_at)
            SELECT risk_id, barangay_id, date, prediction_score,
                   CASE WHEN risk_level = 'severe' THEN 'critical' ELSE risk_level END,
                   generated_at
            FROM risk_assessment
        ");

        DB::statement('DROP TABLE risk_assessment');
        DB::statement('ALTER TABLE risk_assessment_new RENAME TO risk_assessment');
        DB::statement('CREATE INDEX idx_risk_barangay_date ON risk_assessment (barangay_id, date)');
    }

    private function downSqlite(): void
    {
        DB::statement("
            CREATE TABLE risk_assessment_new (
                risk_id INTEGER PRIMARY KEY AUTOINCREMENT,
                barangay_id INTEGER NOT NULL,
                date DATE NOT NULL,
                prediction_score NUMERIC NOT NULL,
                risk_level VARCHAR(255) NOT NULL
                    CHECK (risk_level IN ('low', 'moderate', 'high', 'severe')),
                generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (barangay_id) REFERENCES barangay (barangay_id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            INSERT INTO risk_assessment_new (risk_id, barangay_id, date, prediction_score, risk_level, generated_at)
            SELECT risk_id, barangay_id, date, prediction_score,
                   CASE WHEN risk_level = 'critical' THEN 'severe' ELSE risk_level END,
                   generated_at
            FROM risk_assessment
        ");

        DB::statement('DROP TABLE risk_assessment');
        DB::statement('ALTER TABLE risk_assessment_new RENAME TO risk_assessment');
        DB::statement('CREATE INDEX idx_risk_barangay_date ON risk_assessment (barangay_id, date)');
    }
};
