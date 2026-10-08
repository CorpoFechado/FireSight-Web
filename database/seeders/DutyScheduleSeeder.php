<?php

namespace Database\Seeders;

use App\Models\DutySchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DutyScheduleSeeder extends Seeder
{
    /**
     * Seeds realistic rotating 12-hour shifts for BFP Lian station personnel.
     *
     * Preset shift windows:
     * - Morning Shift: 06:00:00 to 18:00:00 (6:00 AM – 6:00 PM)
     * - Night Shift:   18:00:00 to 06:00:00 (6:00 PM – 6:00 AM)
     *
     * Covers 5 calendar weeks (Sept 7, 2026 through Oct 11, 2026).
     */
    public function run(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DutySchedule::truncate();

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $admin = User::where('role', User::ROLE_BFP_ADMIN)->first()
            ?? User::where('email', 'admin@firesight.bfp.lian')->first()
            ?? User::first();

        if (! $admin) {
            $this->command?->warn('No admin user found for DutyScheduleSeeder.');

            return;
        }

        // Active field personnel
        $personnel = User::where('role', User::ROLE_BFP_PERSONNEL)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('id')
            ->get();

        if ($personnel->isEmpty()) {
            $this->command?->warn('No BFP personnel found to schedule shifts for.');

            return;
        }

        // Divide active personnel into two operational teams (Platoon Alpha & Platoon Bravo)
        $half = (int) ceil($personnel->count() / 2);
        $platoonAlpha = $personnel->slice(0, $half)->values();
        $platoonBravo = $personnel->slice($half)->values();

        // 5 weeks: from 2026-09-07 (Monday) to 2026-10-11 (Sunday)
        $startDate = Carbon::create(2026, 9, 7);
        $totalDays = 35;

        // Shift rotation:
        // On 2026-09-24 (day offset 17: Thursday), Platoon Alpha is on Morning (06:00-18:00)
        // so they are currently ON DUTY during the afternoon!
        // We set up a 4-day rotating cycle:
        // Day 0: Alpha Morning, Bravo Night
        // Day 1: Alpha Morning, Bravo Night
        // Day 2: Bravo Morning, Alpha Night
        // Day 3: Bravo Morning, Alpha Night
        //
        // On day offset 17 (2026-09-24): 17 % 4 = 1 => Alpha Morning, Bravo Night! Perfect!
        for ($i = 0; $i < $totalDays; $i++) {
            $currentDate = $startDate->copy()->addDays($i)->toDateString();
            $cycle = $i % 4;

            if ($cycle === 0 || $cycle === 1) {
                // Alpha works Morning (06:00 - 18:00)
                foreach ($platoonAlpha as $user) {
                    DutySchedule::create([
                        'user_id' => $user->id,
                        'duty_date' => $currentDate,
                        'time_start' => '06:00:00',
                        'time_end' => '18:00:00',
                        'created_by' => $admin->id,
                    ]);
                }

                // Bravo works Night (18:00 - 06:00)
                foreach ($platoonBravo as $user) {
                    DutySchedule::create([
                        'user_id' => $user->id,
                        'duty_date' => $currentDate,
                        'time_start' => '18:00:00',
                        'time_end' => '06:00:00',
                        'created_by' => $admin->id,
                    ]);
                }
            } else {
                // Bravo works Morning (06:00 - 18:00)
                foreach ($platoonBravo as $user) {
                    DutySchedule::create([
                        'user_id' => $user->id,
                        'duty_date' => $currentDate,
                        'time_start' => '06:00:00',
                        'time_end' => '18:00:00',
                        'created_by' => $admin->id,
                    ]);
                }

                // Alpha works Night (18:00 - 06:00)
                foreach ($platoonAlpha as $user) {
                    DutySchedule::create([
                        'user_id' => $user->id,
                        'duty_date' => $currentDate,
                        'time_start' => '18:00:00',
                        'time_end' => '06:00:00',
                        'created_by' => $admin->id,
                    ]);
                }
            }
        }
    }
}
