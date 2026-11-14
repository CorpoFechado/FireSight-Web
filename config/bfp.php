<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BFP Station Details
    |--------------------------------------------------------------------------
    |
    | Used in the header of generated official documents such as the
    | Spot Investigation Report (SIR).
    |
    */

    'station' => [
        'agency_lines' => [
            'Republic of the Philippines',
            'Department of the Interior and Local Government',
        ],
        'office_lines' => [
            'BUREAU OF FIRE PROTECTION',
            'REGION IV-A',
            'PROVINCE OF BATANGAS',
            'LIAN FIRE STATION',
        ],
        'address' => env('BFP_STATION_ADDRESS', 'J.P Rizal, St., Brgy. 1, Lian,, Batangas'),
        'telephone' => env('BFP_STATION_TELEPHONE', '0962-763-3811'),
        'email' => env('BFP_STATION_EMAIL', 'bfp.lianfs02@gmail.com'),
        'municipality' => 'Lian',
        'province' => 'Batangas',
    ],

    /*
    |--------------------------------------------------------------------------
    | Spot Investigation Report (SIR)
    |--------------------------------------------------------------------------
    |
    | Logo paths are absolute; missing files are skipped silently so the
    | document can still be generated before the images are provided.
    |
    */

    'spot_report' => [
        'form_code' => 'BFP-QSF-FAID-002 Rev. 02 (02.03.25)',
        'memo_for' => 'Acting Municipal Fire Marshal',
        'subject' => 'Spot Investigation Report (SIR)',
        'disposition' => 'Progress Investigation Report to follow.',
        'signatories' => [
            'investigator' => 'Fire Arson Investigator',
            'noted_by' => 'Chief, IIU',
            'approved_by' => 'Acting Municipal Fire Marshal',
        ],
        'logos' => [
            'left' => resource_path('images/bfp/bfp-seal.png'),
            'center' => resource_path('images/bfp/bagong-pilipinas.png'),
            'right' => resource_path('images/bfp/lian-fs.png'),
        ],
    ],

];
