<?php

return [
    'rotation' => [
        'anchor_week_start' => env('DUTY_ANCHOR_WEEK_START', '2026-10-12'), // a Monday where Duty A is on duty
        'anchor_group' => env('DUTY_ANCHOR_GROUP', 'A'),
        /**
         * Changeover time on Monday (app timezone).
         * Placeholder until BFP Lian confirms the actual turnover time.
         */
        'changeover_time' => env('DUTY_CHANGEOVER_TIME', '00:00'),
    ],
];
