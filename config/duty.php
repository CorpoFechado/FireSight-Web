<?php

return [
    'rotation' => [
        'anchor_week_start' => env('DUTY_ANCHOR_WEEK_START', '2026-10-12'), // a Monday where Duty B is on duty
        'anchor_group' => env('DUTY_ANCHOR_GROUP', 'B'),
        /**
         * Changeover time on Monday (app timezone).
         * Placeholder until BFP Lian confirms the actual turnover time.
         */
        'changeover_time' => env('DUTY_CHANGEOVER_TIME', '00:00'),
    ],
    'office_hours' => [
        'days' => [1, 2, 3, 4, 5],
        'start' => env('DUTY_OFFICE_HOURS_START', '08:00'),
        'end' => env('DUTY_OFFICE_HOURS_END', '17:00'),
    ],
];
