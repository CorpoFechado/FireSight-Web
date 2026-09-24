<?php

namespace Database\Seeders;

use App\Models\BfpPersonnelDetails;
use App\Models\CommunityReport;
use App\Models\IncidentRecord;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IncidentReportSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign keys while re-seeding reports
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        DB::table('incident_record')->truncate();
        DB::table('report_status_history')->truncate();
        DB::table('report_link')->truncate();
        DB::table('community_report')->truncate();

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // ── Ensure actual BFP personnel with ranks exist in the database ─────
        $personnelConfigs = [
            [
                'email' => 'admin@firesight.bfp.lian',
                'first_name' => 'Ricardo',
                'last_name' => 'Dela Cruz',
                'role' => User::ROLE_BFP_ADMIN,
                'contact_number' => '09171234567',
                'username' => 'rdelacruz',
                'rank' => 'Senior Fire Officer 4',
                'emp' => 'EMP-001',
            ],
            [
                'email' => 'personnel@firesight.bfp.lian',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09181234568',
                'username' => 'msantos',
                'rank' => 'Fire Officer 2',
                'emp' => 'EMP-002',
            ],
            [
                'email' => 'mcorpuz@firesight.bfp.lian',
                'first_name' => 'Maria',
                'last_name' => 'Corpuz',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09445310485',
                'username' => 'mcorpuz',
                'rank' => 'Inspector',
                'emp' => 'EMP-003',
            ],
            [
                'email' => 'bsantos@firesight.bfp.lian',
                'first_name' => 'Bernardo',
                'last_name' => 'Santos',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09181994523',
                'username' => 'bsantos',
                'rank' => 'Fire Officer 3',
                'emp' => 'EMP-005',
            ],
            [
                'email' => 'amalabanan@firesight.bfp.lian',
                'first_name' => 'Arnel',
                'last_name' => 'Malabanan',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09178492780',
                'username' => 'amalabanan',
                'rank' => 'Inspector',
                'emp' => 'EMP-006',
            ],
            [
                'email' => 'mmercado@firesight.bfp.lian',
                'first_name' => 'Michael',
                'last_name' => 'Mercado',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09735126461',
                'username' => 'mmercado',
                'rank' => 'Senior Fire Officer 3',
                'emp' => 'EMP-007',
            ],
            [
                'email' => 'mrivera@firesight.bfp.lian',
                'first_name' => 'Mark',
                'last_name' => 'Rivera',
                'role' => User::ROLE_BFP_PERSONNEL,
                'contact_number' => '09156977991',
                'username' => 'mrivera',
                'rank' => 'Senior Fire Officer 2',
                'emp' => 'EMP-008',
            ],
        ];

        foreach ($personnelConfigs as $cfg) {
            $user = User::firstOrCreate(
                ['email' => $cfg['email']],
                [
                    'role' => $cfg['role'],
                    'first_name' => $cfg['first_name'],
                    'last_name' => $cfg['last_name'],
                    'contact_number' => $cfg['contact_number'],
                    'username' => $cfg['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            BfpPersonnelDetails::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'rank' => $cfg['rank'],
                    'station_assigned' => 'BFP Lian Fire Station',
                    'employee_number' => $cfg['emp'],
                ]
            );
        }

        // Personnel handles for history transitions
        $sfo4DelaCruz = User::where('email', 'admin@firesight.bfp.lian')->first();
        $fo2Santos = User::where('email', 'personnel@firesight.bfp.lian')->first();
        $inspCorpuz = User::where('email', 'mcorpuz@firesight.bfp.lian')->first() ?? $sfo4DelaCruz;
        $fo3Santos = User::where('email', 'bsantos@firesight.bfp.lian')->first() ?? $fo2Santos;
        $inspMalabanan = User::where('email', 'amalabanan@firesight.bfp.lian')->first() ?? $sfo4DelaCruz;
        $sfo3Mercado = User::where('email', 'mmercado@firesight.bfp.lian')->first() ?? $sfo4DelaCruz;
        $sfo2Rivera = User::where('email', 'mrivera@firesight.bfp.lian')->first() ?? $fo2Santos;

        // ── Ensure resident reporters exist ──────────────────────────────────
        $residentConfigs = [
            ['email' => 'jrivera@gmail.com', 'first' => 'Jennifer', 'last' => 'Rivera', 'contact' => '09316944844'],
            ['email' => 'eumali@gmail.com', 'first' => 'Ernesto', 'last' => 'Umali', 'contact' => '09436665249'],
            ['email' => 'jgarcia@gmail.com', 'first' => 'Jennifer', 'last' => 'Garcia', 'contact' => '09249585092'],
            ['email' => 'mpascual@gmail.com', 'first' => 'Manuel', 'last' => 'Pascual', 'contact' => '09679908599'],
            ['email' => 'lmalabanan@gmail.com', 'first' => 'Leonora', 'last' => 'Malabanan', 'contact' => '09218212356'],
            ['email' => 'mfernandez@gmail.com', 'first' => 'Michael', 'last' => 'Fernandez', 'contact' => '09541735568'],
            ['email' => 'rvillanueva@gmail.com', 'first' => 'Rolando', 'last' => 'Villanueva', 'contact' => '09840256940'],
            ['email' => 'rbautista@gmail.com', 'first' => 'Rosario', 'last' => 'Bautista', 'contact' => '09823715057'],
            ['email' => 'jcorpuz@gmail.com', 'first' => 'Juan', 'last' => 'Corpuz', 'contact' => '09922751234'],
            ['email' => 'kmendoza@gmail.com', 'first' => 'Kristine', 'last' => 'Mendoza', 'contact' => '09695119047'],
            ['email' => 'apascual@gmail.com', 'first' => 'Alfredo', 'last' => 'Pascual', 'contact' => '09419953851'],
            ['email' => 'aumali@gmail.com', 'first' => 'Ana', 'last' => 'Umali', 'contact' => '09394703907'],
        ];

        foreach ($residentConfigs as $r) {
            User::firstOrCreate(
                ['email' => $r['email']],
                [
                    'role' => User::ROLE_RESIDENT,
                    'first_name' => $r['first'],
                    'last_name' => $r['last'],
                    'contact_number' => $r['contact'],
                    'username' => strtolower($r['first'][0].$r['last']),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        $resRivera = User::where('email', 'jrivera@gmail.com')->first();
        $resUmaliE = User::where('email', 'eumali@gmail.com')->first();
        $resGarcia = User::where('email', 'jgarcia@gmail.com')->first();
        $resPascualM = User::where('email', 'mpascual@gmail.com')->first();
        $resMalabananL = User::where('email', 'lmalabanan@gmail.com')->first();
        $resFernandez = User::where('email', 'mfernandez@gmail.com')->first();
        $resVillanuevaR = User::where('email', 'rvillanueva@gmail.com')->first();
        $resBautistaR = User::where('email', 'rbautista@gmail.com')->first();
        $resCorpuzJ = User::where('email', 'jcorpuz@gmail.com')->first();
        $resMendozaK = User::where('email', 'kmendoza@gmail.com')->first();
        $resPascualA = User::where('email', 'apascual@gmail.com')->first();
        $resUmaliA = User::where('email', 'aumali@gmail.com')->first();

        $now = Carbon::now();

        // ── 1. PENDING REPORTS (3 records, NO incident_record) ────────────────
        $p1 = CommunityReport::create([
            'report_id' => 1,
            'user_id' => $resRivera->id,
            'reporter_name' => 'Jennifer Rivera',
            'contact_number' => '09316944844',
            'description' => 'Visible dark smoke rising from a residential roof near the elementary school.',
            'report_image' => null,
            'latitude' => 13.9965,
            'longitude' => 120.6550,
            'barangay_id' => 1, // Poblacion 1
            'status' => CommunityReport::STATUS_PENDING,
            'created_at' => $now->copy()->subMinutes(15),
            'updated_at' => $now->copy()->subMinutes(15),
        ]);
        ReportStatusHistory::create([
            'report_id' => $p1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Community report submitted via FireSight mobile app with verified GPS coordinates.',
            'changed_by' => $resRivera->id,
            'created_at' => $now->copy()->subMinutes(15),
        ]);

        $p2 = CommunityReport::create([
            'report_id' => 2,
            'user_id' => $resUmaliE->id,
            'reporter_name' => 'Ernesto Umali',
            'contact_number' => '09436665249',
            'description' => 'Unattended bonfire spreading close to dry bamboo grove and beach cottages.',
            'report_image' => null,
            'latitude' => 13.9575,
            'longitude' => 120.6272,
            'barangay_id' => 13, // Matabungkay
            'status' => CommunityReport::STATUS_PENDING,
            'created_at' => $now->copy()->subMinutes(35),
            'updated_at' => $now->copy()->subMinutes(35),
        ]);
        ReportStatusHistory::create([
            'report_id' => $p2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Community report submitted via FireSight mobile app. Threat of spreading flames reported.',
            'changed_by' => $resUmaliE->id,
            'created_at' => $now->copy()->subMinutes(35),
        ]);

        $p3 = CommunityReport::create([
            'report_id' => 3,
            'user_id' => $resGarcia->id,
            'reporter_name' => 'Jennifer Garcia',
            'contact_number' => '09249585092',
            'description' => 'Burning electrical odor and sparks along service drop cable outside a residence.',
            'report_image' => null,
            'latitude' => 14.0201,
            'longitude' => 120.6473,
            'barangay_id' => 4, // Binubusan
            'status' => CommunityReport::STATUS_PENDING,
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ]);
        ReportStatusHistory::create([
            'report_id' => $p3->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Community report submitted via FireSight mobile app.',
            'changed_by' => $resGarcia->id,
            'created_at' => $now->copy()->subHours(1),
        ]);

        // ── 2. ACCEPTED REPORTS (2 records, NO incident_record) ───────────────
        $a1 = CommunityReport::create([
            'report_id' => 4,
            'user_id' => $resPascualM->id,
            'reporter_name' => 'Manuel Pascual',
            'contact_number' => '09679908599',
            'description' => 'Sparking overhead distribution transformer with burning leaves underneath.',
            'report_image' => null,
            'latitude' => 14.0082,
            'longitude' => 120.6380,
            'barangay_id' => 6, // Bungahan
            'status' => CommunityReport::STATUS_ACCEPTED,
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(2)->addMinutes(10),
        ]);
        ReportStatusHistory::create([
            'report_id' => $a1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Incident reported by citizen via mobile app.',
            'changed_by' => $resPascualM->id,
            'created_at' => $now->copy()->subHours(2),
        ]);
        ReportStatusHistory::create([
            'report_id' => $a1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Report verified by duty verifier FO2 Maria Santos; assigned to Bungahan. Awaiting crew availability.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $now->copy()->subHours(2)->addMinutes(10),
        ]);

        $a2 = CommunityReport::create([
            'report_id' => 5,
            'user_id' => $resMalabananL->id,
            'reporter_name' => 'Leonora Malabanan',
            'contact_number' => '09218212356',
            'description' => 'Backyard agricultural burning that began spreading to roadside brush.',
            'report_image' => null,
            'latitude' => 13.9980,
            'longitude' => 120.6550,
            'barangay_id' => 14, // Prenza
            'status' => CommunityReport::STATUS_ACCEPTED,
            'created_at' => $now->copy()->subHours(3),
            'updated_at' => $now->copy()->subHours(3)->addMinutes(15),
        ]);
        ReportStatusHistory::create([
            'report_id' => $a2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Incident reported by citizen via mobile app.',
            'changed_by' => $resMalabananL->id,
            'created_at' => $now->copy()->subHours(3),
        ]);
        ReportStatusHistory::create([
            'report_id' => $a2->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Incident accepted by duty officer FO3 Bernardo Santos; assigned to Prenza. Contacted barangay hall.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $now->copy()->subHours(3)->addMinutes(15),
        ]);

        // ── 3. DISPATCHED REPORTS (2 records, NO incident_record) ─────────────
        $d1 = CommunityReport::create([
            'report_id' => 6,
            'user_id' => $resFernandez->id,
            'reporter_name' => 'Michael Fernandez',
            'contact_number' => '09541735568',
            'description' => 'Engine fire on a passenger jeepney stalled near highway junction.',
            'report_image' => null,
            'latitude' => 13.9664,
            'longitude' => 120.6465,
            'barangay_id' => 16, // San Diego
            'status' => CommunityReport::STATUS_DISPATCHED,
            'created_at' => $now->copy()->subHours(1)->subMinutes(30),
            'updated_at' => $now->copy()->subHours(1)->subMinutes(15),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Report received from motorist.',
            'changed_by' => $resFernandez->id,
            'created_at' => $now->copy()->subHours(1)->subMinutes(30),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Verified vehicular hazard along national highway; assigned to San Diego.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $now->copy()->subHours(1)->subMinutes(25),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 dispatched from Lian Central Fire Station. Responders en route under SFO3 Michael Mercado.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $now->copy()->subHours(1)->subMinutes(15),
        ]);

        $d2 = CommunityReport::create([
            'report_id' => 7,
            'user_id' => $resVillanuevaR->id,
            'reporter_name' => 'Rolando Villanueva',
            'contact_number' => '09840256940',
            'description' => 'Smoke emerging from secondary storage room in commercial compound.',
            'report_image' => null,
            'latitude' => 13.9822,
            'longitude' => 120.6750,
            'barangay_id' => 11, // Malaruhatan
            'status' => CommunityReport::STATUS_DISPATCHED,
            'created_at' => $now->copy()->subHours(2)->subMinutes(40),
            'updated_at' => $now->copy()->subHours(2)->subMinutes(20),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Commercial structure smoke incident submitted.',
            'changed_by' => $resVillanuevaR->id,
            'created_at' => $now->copy()->subHours(2)->subMinutes(40),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Report accepted by duty supervisor SFO4 Ricardo Dela Cruz; assigned to Malaruhatan.',
            'changed_by' => $sfo4DelaCruz->id,
            'created_at' => $now->copy()->subHours(2)->subMinutes(30),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Tanker 1 and Engine 2 dispatched with 6 responders under SFO2 Mark Rivera. ETA 8 minutes.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $now->copy()->subHours(2)->subMinutes(20),
        ]);

        // Link d1 and d2 as related reports
        DB::table('report_link')->insert([
            'main_report_id' => 6,
            'related_report_id' => 7,
        ]);

        // ── 4. RESOLVED REPORTS (4 records, WITH incident_record) ─────────────
        $r1 = CommunityReport::create([
            'report_id' => 8,
            'user_id' => $resBautistaR->id,
            'reporter_name' => 'Rosario Bautista',
            'contact_number' => '09823715057',
            'description' => 'Two-storey wooden and concrete residential house caught fire from kitchen area.',
            'report_image' => null,
            'latitude' => 13.9915,
            'longitude' => 120.6411,
            'barangay_id' => 2, // Poblacion 2
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(1)->setHour(14)->setMinute(10),
            'updated_at' => $now->copy()->subDays(1)->setHour(15)->setMinute(30),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Residential fire report received via mobile app.',
            'changed_by' => $resBautistaR->id,
            'created_at' => $r1->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $r1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Accepted immediately as high-priority structure fire in Poblacion 2.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $r1->created_at->copy()->addMinutes(8),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r1->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 and Engine 2 dispatched with 6 firefighters under SFO2 Mark Rivera.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $r1->created_at->copy()->addMinutes(12),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r1->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Fire extinguished and declared fire out by ground commander Inspector Arnel Malabanan. Post-incident assessment completed.',
            'changed_by' => $inspMalabanan->id,
            'created_at' => $r1->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $r1->report_id,
            'barangay_id' => $r1->barangay_id,
            'incident_datetime' => $r1->updated_at,
            'incident_type' => 'residential_fire',
            'severity_level' => 'high',
            'cause_of_fire' => 'Faulty electrical wiring in kitchen ceiling',
            'casualties' => 0,
            'notes' => 'Fire declared out after 45 minutes of active suppression. No adjacent structures damaged.',
        ]);

        $r2 = CommunityReport::create([
            'report_id' => 9,
            'user_id' => $resCorpuzJ->id,
            'reporter_name' => 'Juan Corpuz',
            'contact_number' => '09922751234',
            'description' => 'Commercial grocery store caught fire along national road.',
            'report_image' => null,
            'latitude' => 13.9749,
            'longitude' => 120.6622,
            'barangay_id' => 3, // Balibago
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(2)->setHour(9)->setMinute(15),
            'updated_at' => $now->copy()->subDays(2)->setHour(10)->setMinute(40),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Commercial fire incident reported.',
            'changed_by' => $resCorpuzJ->id,
            'created_at' => $r2->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $r2->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Verified and accepted by FO3 Bernardo Santos; assigned to Balibago.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $r2->created_at->copy()->addMinutes(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r2->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 and Tanker 1 dispatched from Lian Station under SFO3 Michael Mercado.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $r2->created_at->copy()->addMinutes(10),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r2->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Fire fully controlled and extinguished under Inspector Maria Corpuz. Structure secured.',
            'changed_by' => $inspCorpuz->id,
            'created_at' => $r2->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $r2->report_id,
            'barangay_id' => $r2->barangay_id,
            'incident_datetime' => $r2->updated_at,
            'incident_type' => 'commercial_fire',
            'severity_level' => 'moderate',
            'cause_of_fire' => 'Overheated refrigeration compressor',
            'casualties' => 0,
            'notes' => 'Damage contained to inventory backroom. Merchandise salvaged.',
        ]);

        $r3 = CommunityReport::create([
            'report_id' => 10,
            'user_id' => $resMendozaK->id,
            'reporter_name' => 'Kristine Mendoza',
            'contact_number' => '09695119047',
            'description' => 'Delivery van caught fire while parked on roadside.',
            'report_image' => null,
            'latitude' => 13.9848,
            'longitude' => 120.6647,
            'barangay_id' => 10, // Lumaniag
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(3)->setHour(16)->setMinute(20),
            'updated_at' => $now->copy()->subDays(3)->setHour(17)->setMinute(10),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r3->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Report submitted via mobile app.',
            'changed_by' => $resMendozaK->id,
            'created_at' => $r3->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $r3->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Report accepted by FO2 Maria Santos; assigned to Lumaniag.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $r3->created_at->copy()->addMinutes(6),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r3->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 dispatched to vehicular incident under SFO2 Mark Rivera.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $r3->created_at->copy()->addMinutes(12),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r3->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Vehicle fire contained and extinguished with AFFF foam under SFO4 Ricardo Dela Cruz.',
            'changed_by' => $sfo4DelaCruz->id,
            'created_at' => $r3->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $r3->report_id,
            'barangay_id' => $r3->barangay_id,
            'incident_datetime' => $r3->updated_at,
            'incident_type' => 'vehicular_fire',
            'severity_level' => 'low',
            'cause_of_fire' => 'Battery terminal short circuit',
            'casualties' => 0,
            'notes' => 'Engine bay damage only. Driver evacuated safely before arrival.',
        ]);

        $r4 = CommunityReport::create([
            'report_id' => 11,
            'user_id' => $resPascualA->id,
            'reporter_name' => 'Alfredo Pascual',
            'contact_number' => '09419953851',
            'description' => 'Spreading grass and rubbish fire along vacant lot boundary.',
            'report_image' => null,
            'latitude' => 14.0128,
            'longitude' => 120.6299,
            'barangay_id' => 8, // Kapito
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(4)->setHour(11)->setMinute(0),
            'updated_at' => $now->copy()->subDays(4)->setHour(11)->setMinute(45),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r4->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Community report submitted.',
            'changed_by' => $resPascualA->id,
            'created_at' => $r4->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $r4->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Report accepted by FO3 Bernardo Santos; assigned to Kapito.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $r4->created_at->copy()->addMinutes(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r4->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Brush fire response unit dispatched under SFO3 Michael Mercado.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $r4->created_at->copy()->addMinutes(10),
        ]);
        ReportStatusHistory::create([
            'report_id' => $r4->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Rubbish fire extinguished without property damage by Inspector Arnel Malabanan.',
            'changed_by' => $inspMalabanan->id,
            'created_at' => $r4->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $r4->report_id,
            'barangay_id' => $r4->barangay_id,
            'incident_datetime' => $r4->updated_at,
            'incident_type' => 'rubbish_fire',
            'severity_level' => 'low',
            'cause_of_fire' => 'Discarded lighted cigarette butt',
            'casualties' => 0,
            'notes' => 'Dry brush fire extinguished quickly by responding unit.',
        ]);

        // ── 5. INVALID REPORT (1 record, NO incident_record) ──────────────────
        $inv = CommunityReport::create([
            'report_id' => 12,
            'user_id' => $resUmaliA->id,
            'reporter_name' => 'Ana Umali',
            'contact_number' => '09394703907',
            'description' => 'Loud explosive bang and smoke seen behind commercial garage.',
            'report_image' => null,
            'latitude' => 14.0002,
            'longitude' => 120.6611,
            'barangay_id' => 3, // Balibago
            'status' => CommunityReport::STATUS_INVALID,
            'created_at' => $now->copy()->subDays(1)->setHour(8)->setMinute(0),
            'updated_at' => $now->copy()->subDays(1)->setHour(8)->setMinute(25),
        ]);
        ReportStatusHistory::create([
            'report_id' => $inv->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Report received regarding alleged explosion sound.',
            'changed_by' => $resUmaliA->id,
            'created_at' => $inv->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $inv->report_id,
            'status' => CommunityReport::STATUS_INVALID,
            'notes' => 'Barangay tanod on site verified it was a tire blowout, not a fire emergency. Marked invalid by SFO4 Ricardo Dela Cruz.',
            'changed_by' => $sfo4DelaCruz->id,
            'created_at' => $inv->updated_at,
        ]);
    }
}
