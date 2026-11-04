<?php

use App\Enums\AlarmLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeAlarmLevelColumns(AlarmLevel::values());
    }

    public function down(): void
    {
        DB::table('incident_record')
            ->where('alarm_level', AlarmLevel::Verification->value)
            ->update(['alarm_level' => AlarmLevel::FirstAlarm->value]);

        DB::table('afor_report')
            ->where('alarm_level', AlarmLevel::Verification->value)
            ->update(['alarm_level' => AlarmLevel::FirstAlarm->value]);

        $levels = array_values(array_filter(
            AlarmLevel::values(),
            fn (string $level): bool => $level !== AlarmLevel::Verification->value,
        ));

        $this->changeAlarmLevelColumns($levels);
    }

    /**
     * @param  list<string>  $levels
     */
    private function changeAlarmLevelColumns(array $levels): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $aforReports = DB::table('afor_report')
                ->get()
                ->map(fn (object $report): array => (array) $report)
                ->all();

            $this->changeAlarmLevelColumn('incident_record', $levels);

            if ($aforReports !== []) {
                DB::table('afor_report')->insert($aforReports);
            }

            $this->changeAlarmLevelColumn('afor_report', $levels);

            return;
        }

        $this->changeAlarmLevelColumn('incident_record', $levels);
        $this->changeAlarmLevelColumn('afor_report', $levels);
    }

    /**
     * @param  list<string>  $levels
     */
    private function changeAlarmLevelColumn(string $tableName, array $levels): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($levels) {
            $table->enum('alarm_level', $levels)->nullable()->change();
        });
    }
};
