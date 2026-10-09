<?php

namespace App\Enums;

enum AlarmLevel: string
{
    case FirstAlarm = '1st_alarm';
    case SecondAlarm = '2nd_alarm';
    case ThirdAlarm = '3rd_alarm';
    case FourthAlarm = '4th_alarm';
    case FifthAlarm = '5th_alarm';
    case TaskForceAlpha = 'task_force_alpha';
    case TaskForceBravo = 'task_force_bravo';
    case TaskForceCharlie = 'task_force_charlie';
    case TaskForceDelta = 'task_force_delta';
    case TaskForceEcho = 'task_force_echo';
    case TaskForceHotel = 'task_force_hotel';
    case TaskForceIndia = 'task_force_india';
    case GeneralAlarm = 'general_alarm';

    public function label(): string
    {
        return match ($this) {
            self::FirstAlarm => '1st Alarm',
            self::SecondAlarm => '2nd Alarm',
            self::ThirdAlarm => '3rd Alarm',
            self::FourthAlarm => '4th Alarm',
            self::FifthAlarm => '5th Alarm',
            self::TaskForceAlpha => 'Task Force Alpha',
            self::TaskForceBravo => 'Task Force Bravo',
            self::TaskForceCharlie => 'Task Force Charlie',
            self::TaskForceDelta => 'Task Force Delta',
            self::TaskForceEcho => 'Task Force Echo',
            self::TaskForceHotel => 'Task Force Hotel',
            self::TaskForceIndia => 'Task Force India',
            self::GeneralAlarm => 'General Alarm',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Alarm levels considered critical for active incident KPIs and alerting.
     * Includes 5th alarm, all task force escalation stages, and general alarm.
     *
     * @return list<string>
     */
    public static function criticalValues(): array
    {
        return [
            self::FifthAlarm->value,
            self::TaskForceAlpha->value,
            self::TaskForceBravo->value,
            self::TaskForceCharlie->value,
            self::TaskForceDelta->value,
            self::TaskForceEcho->value,
            self::TaskForceHotel->value,
            self::TaskForceIndia->value,
            self::GeneralAlarm->value,
        ];
    }

    public function isCritical(): bool
    {
        return in_array($this->value, self::criticalValues(), true);
    }
}
