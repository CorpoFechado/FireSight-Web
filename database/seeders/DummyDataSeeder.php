<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Loads the hand-crafted dummy dataset (community reports, incident
     * records, risk assessments, announcements, notifications, plus
     * additional resident users) from a raw .sql file.
     *
     * Assumes BfpAccountSeeder + BfpPersonnelSeeder ran first and occupy
     * ids 1–8 and 34–43.
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
