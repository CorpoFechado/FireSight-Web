<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Loads the hand-crafted dummy dataset (community reports, incident
     * records, risk assessments, announcements, notifications, plus
     * additional personnel/resident users) from a raw .sql file.
     *
     * Assumes BarangaySeeder and BfpAccountSeeder already ran — this file's
     * rows start at the IDs that come right after the admin/personnel test
     * accounts those seeders create (user_id 3+, etc).
     */
    public function run(): void
    {
        $path = __DIR__.'/sql/firesight_dummy_data.sql';

        if (! file_exists($path)) {
            $this->command?->warn('Skipped: '.$path.' not found.');

            return;
        }

        DB::unprepared(file_get_contents($path));
    }
}
