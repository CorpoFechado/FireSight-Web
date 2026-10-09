<?php

namespace Database\Seeders;

use App\Enums\AlarmLevel;
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
    /**
     * Seeds realistic incident reports, history, and incident records
     * so that analytics, trend charts, risk rankings, and KPIs
     * reflect rich, authentic data across all months and barangays.
     */
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

        // ── 1. Ensure actual BFP personnel exist ──────────────────────────────
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

        $bfpResponders = [$fo2Santos, $inspCorpuz, $fo3Santos, $inspMalabanan, $sfo3Mercado, $sfo2Rivera];

        // ── 2. Ensure resident reporters exist ──────────────────────────────
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
            ['email' => 'rmalabanan@gmail.com', 'first' => 'Ricardo', 'last' => 'Malabanan', 'contact' => '09434027113'],
            ['email' => 'egarcia@gmail.com', 'first' => 'Erlinda', 'last' => 'Garcia', 'contact' => '09556448196'],
            ['email' => 'mmalabanan@gmail.com', 'first' => 'Marilou', 'last' => 'Malabanan', 'contact' => '09538302652'],
            ['email' => 'arosales@gmail.com', 'first' => 'Angelica', 'last' => 'Rosales', 'contact' => '09258449460'],
            ['email' => 'jmarasigan@gmail.com', 'first' => 'Josefina', 'last' => 'Marasigan', 'contact' => '09932893941'],
        ];

        $residents = [];
        foreach ($residentConfigs as $r) {
            $user = User::firstOrCreate(
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
            $residents[] = $user;
        }

        // Lian 19 barangays coordinates
        $barangayCoordinates = [
            1 => ['lat' => 14.0353356, 'lng' => 120.6511238, 'name' => 'Poblacion 1'],
            2 => ['lat' => 14.0381440, 'lng' => 120.6512847, 'name' => 'Poblacion 2'],
            3 => ['lat' => 14.0404400, 'lng' => 120.6506992, 'name' => 'Poblacion 3'],
            4 => ['lat' => 14.0379271, 'lng' => 120.6536966, 'name' => 'Poblacion 4'],
            5 => ['lat' => 14.0412322, 'lng' => 120.6529497, 'name' => 'Poblacion 5'],
            6 => ['lat' => 13.9422816, 'lng' => 120.6508579, 'name' => 'Balibago'],
            7 => ['lat' => 14.0336630, 'lng' => 120.6806919, 'name' => 'Bagong Pook'],
            8 => ['lat' => 13.9687652, 'lng' => 120.6382428, 'name' => 'Binubusan'],
            9 => ['lat' => 14.0468166, 'lng' => 120.6380702, 'name' => 'Bungahan'],
            10 => ['lat' => 13.9724523, 'lng' => 120.6636961, 'name' => 'Cumba'],
            11 => ['lat' => 13.9967267, 'lng' => 120.6718058, 'name' => 'Humayingan'],
            12 => ['lat' => 14.0385226, 'lng' => 120.6653881, 'name' => 'Malaruhatan'],
            13 => ['lat' => 13.9465891, 'lng' => 120.6252355, 'name' => 'Matabungkay'],
            14 => ['lat' => 14.0124515, 'lng' => 120.6396729, 'name' => 'Prenza'],
            15 => ['lat' => 13.9952352, 'lng' => 120.6321466, 'name' => 'Lumaniag'],
            16 => ['lat' => 13.9640345, 'lng' => 120.6176000, 'name' => 'Luyahan'],
            17 => ['lat' => 14.0168316, 'lng' => 120.6704694, 'name' => 'Kapito'],
            18 => ['lat' => 14.0342584, 'lng' => 120.6338541, 'name' => 'San Diego'],
            19 => ['lat' => 13.9867855, 'lng' => 120.6518731, 'name' => 'Puting Kahoy'],
        ];

        $now = Carbon::create(2026, 9, 24, 15, 58, 10);

        // ── 3. Active & Recent Incidents for Today & This Week ────────────────
        // A. PENDING 1: Today, ~25 mins ago
        $p1User = $residents[0];
        $p1 = CommunityReport::create([
            'report_id' => 1,
            'user_id' => $p1User->id,
            'reporter_name' => $p1User->name,
            'contact_number' => $p1User->contact_number,
            'description' => 'Thick black smoke and visible flames billowing from a residential kitchen roof near the elementary school.',
            'report_image' => 'report_images/report_8.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9420,
            'ai_verified_at' => $now->copy()->subMinutes(24),
            'latitude' => 14.0353 + 0.0012,
            'longitude' => 120.6511 - 0.0008,
            'barangay_id' => 1, // Poblacion 1
            'status' => CommunityReport::STATUS_PENDING,
            'created_at' => $now->copy()->subMinutes(25),
            'updated_at' => $now->copy()->subMinutes(25),
        ]);
        ReportStatusHistory::create([
            'report_id' => $p1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Community report submitted via FireSight mobile app with verified GPS coordinates.',
            'changed_by' => $p1User->id,
            'created_at' => $p1->created_at,
        ]);

        // B. PENDING 2: Today, ~50 mins ago
        $p2User = $residents[1];
        $p2 = CommunityReport::create([
            'report_id' => 2,
            'user_id' => $p2User->id,
            'reporter_name' => $p2User->name,
            'contact_number' => $p2User->contact_number,
            'description' => 'Unattended bonfire spreading close to dry bamboo grove and beach cottage structures.',
            'report_image' => 'report_images/report_2.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9650,
            'ai_verified_at' => $now->copy()->subMinutes(48),
            'latitude' => 13.9465 + 0.0015,
            'longitude' => 120.6252 + 0.0011,
            'barangay_id' => 13, // Matabungkay
            'status' => CommunityReport::STATUS_PENDING,
            'created_at' => $now->copy()->subMinutes(50),
            'updated_at' => $now->copy()->subMinutes(50),
        ]);
        ReportStatusHistory::create([
            'report_id' => $p2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Citizen reported threat of spreading flames towards resort fences.',
            'changed_by' => $p2User->id,
            'created_at' => $p2->created_at,
        ]);

        // C. ACCEPTED 1: Today, ~2 hours ago
        $a1User = $residents[3];
        $a1 = CommunityReport::create([
            'report_id' => 3,
            'user_id' => $a1User->id,
            'reporter_name' => $a1User->name,
            'contact_number' => $a1User->contact_number,
            'description' => 'Sparking overhead distribution transformer with burning dry leaves directly underneath.',
            'report_image' => 'report_images/report_4.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9380,
            'ai_verified_at' => $now->copy()->subHours(2)->addMinute(),
            'latitude' => 14.0468 - 0.0010,
            'longitude' => 120.6380 + 0.0014,
            'barangay_id' => 9, // Bungahan
            'status' => CommunityReport::STATUS_ACCEPTED,
            'created_at' => $now->copy()->subHours(2),
            'updated_at' => $now->copy()->subHours(1)->subMinutes(50),
        ]);
        ReportStatusHistory::create([
            'report_id' => $a1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Incident reported by citizen via mobile app.',
            'changed_by' => $a1User->id,
            'created_at' => $a1->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $a1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Verified by duty verifier FO2 Maria Santos. Assigned to Bungahan. BATELEC power company alerted.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $a1->updated_at,
        ]);

        // D. DISPATCHED 1: Today, ~1.5 hours ago (Critical Active)
        $d1User = $residents[5];
        $d1 = CommunityReport::create([
            'report_id' => 4,
            'user_id' => $d1User->id,
            'reporter_name' => $d1User->name,
            'contact_number' => $d1User->contact_number,
            'description' => 'Heavy black smoke and exploding storage drums in commercial storage depot.',
            'report_image' => 'report_images/report_7.jpg',
            'ai_fire_label' => 'smoke',
            'ai_fire_confidence' => 0.9710,
            'ai_verified_at' => $now->copy()->subHours(1)->subMinutes(29),
            'latitude' => 14.0342 + 0.0018,
            'longitude' => 120.6338 - 0.0012,
            'barangay_id' => 18, // San Diego
            'status' => CommunityReport::STATUS_DISPATCHED,
            'created_at' => $now->copy()->subHours(1)->subMinutes(30),
            'updated_at' => $now->copy()->subHours(1)->subMinutes(15),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Emergency report received regarding chemical warehouse fire.',
            'changed_by' => $d1User->id,
            'created_at' => $d1->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Verified high-hazard industrial warehouse fire. Priority alarm raised by FO3 Bernardo Santos.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $d1->created_at->copy()->addMinutes(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d1->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 and Tanker 1 dispatched from Lian Station under SFO3 Michael Mercado. Responders on scene actively containing boundary.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $d1->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $d1->report_id,
            'barangay_id' => $d1->barangay_id,
            'incident_datetime' => $d1->created_at,
            'incident_type' => 'storage_fire',
            'alarm_level' => '5th_alarm',
            'cause_of_fire' => 'Under active investigation; suspected chemical solvent ignition',
            'casualties' => 0,
            'notes' => 'Mutual aid tanker requested from Nasugbu BFP. Active suppression ongoing.',
        ]);

        // E. RELATED REPORT TO D1 (Commercial Storage Smoke)
        $d2User = $residents[6];
        $d2 = CommunityReport::create([
            'report_id' => 5,
            'user_id' => $d2User->id,
            'reporter_name' => $d2User->name,
            'contact_number' => $d2User->contact_number,
            'description' => 'Strong chemical odor and dense smoke seen across the provincial road from depot.',
            'report_image' => 'report_images/report_6.jpg',
            'ai_fire_label' => 'smoke',
            'ai_fire_confidence' => 0.9320,
            'ai_verified_at' => $now->copy()->subHours(1)->subMinutes(24),
            'latitude' => 14.0342 + 0.0022,
            'longitude' => 120.6338 - 0.0005,
            'barangay_id' => 18, // San Diego
            'status' => CommunityReport::STATUS_DISPATCHED,
            'created_at' => $now->copy()->subHours(1)->subMinutes(25),
            'updated_at' => $now->copy()->subHours(1)->subMinutes(12),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Secondary citizen report for commercial area fire.',
            'changed_by' => $d2User->id,
            'created_at' => $d2->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Merged with main incident record for San Diego commercial depot.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $d2->created_at->copy()->addMinutes(4),
        ]);
        ReportStatusHistory::create([
            'report_id' => $d2->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Linked to Incident #4.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $d2->updated_at,
        ]);
        DB::table('report_link')->insert([
            'main_report_id' => $d1->report_id,
            'related_report_id' => $d2->report_id,
        ]);

        // F. RESOLVED TODAY: Morning ~09:30 AM
        $rTodayUser = $residents[7];
        $rToday = CommunityReport::create([
            'report_id' => 6,
            'user_id' => $rTodayUser->id,
            'reporter_name' => $rTodayUser->name,
            'contact_number' => $rTodayUser->contact_number,
            'description' => 'Kitchen fire caused by liquefied petroleum gas (LPG) stove hose leak in two-storey residential house.',
            'report_image' => 'report_images/report_8.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9850,
            'ai_verified_at' => $now->copy()->setHour(9)->setMinute(16),
            'latitude' => 14.0381 + 0.0010,
            'longitude' => 120.6512 + 0.0007,
            'barangay_id' => 2, // Poblacion 2
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->setHour(9)->setMinute(15),
            'updated_at' => $now->copy()->setHour(10)->setMinute(20),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rToday->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Emergency report submitted via mobile app.',
            'changed_by' => $rTodayUser->id,
            'created_at' => $rToday->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $rToday->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'High-priority residential structure fire verified by FO2 Maria Santos.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $rToday->created_at->copy()->addMinutes(4),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rToday->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 dispatched with 5 firefighters under SFO2 Mark Rivera.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $rToday->created_at->copy()->addMinutes(8),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rToday->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Fire extinguished and declared fire out by ground commander Inspector Arnel Malabanan. Tank removed safely.',
            'changed_by' => $inspMalabanan->id,
            'created_at' => $rToday->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $rToday->report_id,
            'barangay_id' => $rToday->barangay_id,
            'incident_datetime' => $rToday->updated_at,
            'incident_type' => 'residential_fire',
            'alarm_level' => '2nd_alarm',
            'cause_of_fire' => 'Damaged LPG regulator hose connection ignited by pilot burner',
            'casualties' => 0,
            'notes' => 'Confined to dirty kitchen area. No structural damage to main house.',
        ]);

        // G. YESTERDAY INCIDENT 1 (Resolved)
        $rYestUser = $residents[8];
        $rYest = CommunityReport::create([
            'report_id' => 7,
            'user_id' => $rYestUser->id,
            'reporter_name' => $rYestUser->name,
            'contact_number' => $rYestUser->contact_number,
            'description' => 'Commercial grocery store storage freezer caught fire along national road.',
            'report_image' => 'report_images/report_9.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9570,
            'ai_verified_at' => $now->copy()->subDay()->setHour(14)->setMinute(31),
            'latitude' => 13.9422 + 0.0014,
            'longitude' => 120.6508 - 0.0009,
            'barangay_id' => 6, // Balibago
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDay()->setHour(14)->setMinute(30),
            'updated_at' => $now->copy()->subDay()->setHour(15)->setMinute(45),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rYest->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Commercial fire incident reported.',
            'changed_by' => $rYestUser->id,
            'created_at' => $rYest->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $rYest->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Verified and accepted by FO3 Bernardo Santos; assigned to Balibago.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $rYest->created_at->copy()->addMinutes(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rYest->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 and Tanker 1 dispatched from Lian Station under SFO3 Michael Mercado.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $rYest->created_at->copy()->addMinutes(9),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rYest->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Fire extinguished and power isolated by Inspector Maria Corpuz.',
            'changed_by' => $inspCorpuz->id,
            'created_at' => $rYest->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $rYest->report_id,
            'barangay_id' => $rYest->barangay_id,
            'incident_datetime' => $rYest->updated_at,
            'incident_type' => 'commercial_fire',
            'alarm_level' => '2nd_alarm',
            'cause_of_fire' => 'Overheated commercial refrigeration compressor motor',
            'casualties' => 0,
            'notes' => 'Stock in inventory backroom protected by quick response salvage operations.',
        ]);

        // H. YESTERDAY INCIDENT 2 (Invalid report)
        $invUser = $residents[9];
        $inv = CommunityReport::create([
            'report_id' => 8,
            'user_id' => $invUser->id,
            'reporter_name' => $invUser->name,
            'contact_number' => $invUser->contact_number,
            'description' => 'Loud explosion sound and smoke seen behind gas station along provincial road.',
            'report_image' => null,
            'ai_fire_label' => 'no_fire',
            'ai_fire_confidence' => 0.9120,
            'ai_verified_at' => $now->copy()->subDay()->setHour(10)->setMinute(3),
            'latitude' => 13.9687 + 0.0010,
            'longitude' => 120.6382 - 0.0015,
            'barangay_id' => 8, // Binubusan
            'status' => CommunityReport::STATUS_INVALID,
            'created_at' => $now->copy()->subDay()->setHour(10)->setMinute(0),
            'updated_at' => $now->copy()->subDay()->setHour(10)->setMinute(25),
        ]);
        ReportStatusHistory::create([
            'report_id' => $inv->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Citizen suspected gas station fire explosion.',
            'changed_by' => $invUser->id,
            'created_at' => $inv->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $inv->report_id,
            'status' => CommunityReport::STATUS_INVALID,
            'notes' => 'Barangay tanod and BFP inspector verified heavy truck blown tire, no fire or hazard found. Cleared by SFO4 Ricardo Dela Cruz.',
            'changed_by' => $sfo4DelaCruz->id,
            'created_at' => $inv->updated_at,
        ]);

        // I. EARLIER THIS WEEK (Sept 22, Resolved Vehicular Fire)
        $rW1User = $residents[10];
        $rW1 = CommunityReport::create([
            'report_id' => 9,
            'user_id' => $rW1User->id,
            'reporter_name' => $rW1User->name,
            'contact_number' => $rW1User->contact_number,
            'description' => 'Engine fire on passenger jeepney along Matabungkay resort access road.',
            'report_image' => 'report_images/report_6.jpg',
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.9780,
            'ai_verified_at' => $now->copy()->subDays(2)->setHour(11)->setMinute(16),
            'latitude' => 13.9465 - 0.0012,
            'longitude' => 120.6252 + 0.0020,
            'barangay_id' => 13, // Matabungkay
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(2)->setHour(11)->setMinute(15),
            'updated_at' => $now->copy()->subDays(2)->setHour(12)->setMinute(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW1->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Motorist report received.',
            'changed_by' => $rW1User->id,
            'created_at' => $rW1->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW1->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Accepted by FO2 Maria Santos.',
            'changed_by' => $fo2Santos->id,
            'created_at' => $rW1->created_at->copy()->addMinutes(4),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW1->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Engine 1 dispatched under SFO2 Mark Rivera.',
            'changed_by' => $sfo2Rivera->id,
            'created_at' => $rW1->created_at->copy()->addMinutes(8),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW1->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Engine fire extinguished with dry chemical powder. Vehicle towed safely.',
            'changed_by' => $inspMalabanan->id,
            'created_at' => $rW1->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $rW1->report_id,
            'barangay_id' => $rW1->barangay_id,
            'incident_datetime' => $rW1->updated_at,
            'incident_type' => 'vehicular_fire',
            'alarm_level' => '1st_alarm',
            'cause_of_fire' => 'Alternator electrical short circuit ignited leaking motor oil',
            'casualties' => 0,
            'notes' => 'Driver and all 4 passengers evacuated without injuries.',
        ]);

        // J. EARLIER THIS WEEK (Sept 21, Resolved Rubbish Fire)
        $rW2User = $residents[11];
        $rW2 = CommunityReport::create([
            'report_id' => 10,
            'user_id' => $rW2User->id,
            'reporter_name' => $rW2User->name,
            'contact_number' => $rW2User->contact_number,
            'description' => 'Uncontrolled dry brush and cogon grass fire spreading near agricultural perimeter fencing.',
            'report_image' => null,
            'ai_fire_label' => 'fire',
            'ai_fire_confidence' => 0.8870,
            'ai_verified_at' => $now->copy()->subDays(3)->setHour(15)->setMinute(32),
            'latitude' => 14.0168 + 0.0016,
            'longitude' => 120.6704 - 0.0011,
            'barangay_id' => 17, // Kapito
            'status' => CommunityReport::STATUS_RESOLVED,
            'created_at' => $now->copy()->subDays(3)->setHour(15)->setMinute(30),
            'updated_at' => $now->copy()->subDays(3)->setHour(16)->setMinute(25),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW2->report_id,
            'status' => CommunityReport::STATUS_PENDING,
            'notes' => 'Grass fire report submitted.',
            'changed_by' => $rW2User->id,
            'created_at' => $rW2->created_at,
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW2->report_id,
            'status' => CommunityReport::STATUS_ACCEPTED,
            'notes' => 'Accepted by FO3 Bernardo Santos.',
            'changed_by' => $fo3Santos->id,
            'created_at' => $rW2->created_at->copy()->addMinutes(5),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW2->report_id,
            'status' => CommunityReport::STATUS_DISPATCHED,
            'notes' => 'Dispatched under SFO3 Michael Mercado.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $rW2->created_at->copy()->addMinutes(9),
        ]);
        ReportStatusHistory::create([
            'report_id' => $rW2->report_id,
            'status' => CommunityReport::STATUS_RESOLVED,
            'notes' => 'Flames extinguished using fire beaters and booster hose lines.',
            'changed_by' => $sfo3Mercado->id,
            'created_at' => $rW2->updated_at,
        ]);
        IncidentRecord::create([
            'report_id' => $rW2->report_id,
            'barangay_id' => $rW2->barangay_id,
            'incident_datetime' => $rW2->updated_at,
            'incident_type' => 'rubbish_fire',
            'alarm_level' => '1st_alarm',
            'cause_of_fire' => 'Discarded lighted cigarette butt ignited dry cogon grass along roadside',
            'casualties' => 0,
            'notes' => 'Area secured. No residential damage.',
        ]);

        // ── 4. Curated Historical Incidents Generator ─────────────────────────
        // Detailed realistic fire scenarios in Lian, Batangas
        $scenarios = [
            'residential_fire' => [
                ['desc' => 'Two-storey wooden residential home caught fire in bedroom ceiling area.', 'cause' => 'Electrical short circuit from overloaded extension cord in second-floor bedroom', 'alarm_level' => '3rd_alarm'],
                ['desc' => 'Fire broke out in residential kitchen while boiling cooking oil.', 'cause' => 'Unattended cooking pot ignited nearby vinyl wall covering and cabinets', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Residential single-detached house caught fire during power restoration.', 'cause' => 'Voltage power surge ignited aged knob-and-tube electrical wiring in attic', 'alarm_level' => '4th_alarm'],
                ['desc' => 'Lighted candle left burning during evening power brownout ignited curtains.', 'cause' => 'Unattended candle fell onto fabric window drapery', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Overheated floor fan motor ignited living room carpet and sofa foam.', 'cause' => 'Defective electric fan motor with seized bearings overheated and melted plastic housing', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Kitchen fire spreading rapidly across wood-and-concrete residential structure.', 'cause' => 'LPG stove regulator leak ignited by spark from refrigerator relay', 'alarm_level' => '5th_alarm'],
                ['desc' => 'Substandard electrical jumper wire caught fire along roof eaves.', 'cause' => 'Overheated illegal electrical connection caused insulation melting and spark ignition', 'alarm_level' => 'task_force_alpha'],
                ['desc' => 'Residential garage workshop fire ignited by battery charger.', 'cause' => 'Short circuit in lithium motorcycle battery charger left connected overnight', 'alarm_level' => '2nd_alarm'],
            ],
            'commercial_fire' => [
                ['desc' => 'Commercial restaurant exhaust duct fire with heavy grease accumulation.', 'cause' => 'Grease buildup in commercial kitchen exhaust hood ignited by cooking flames', 'alarm_level' => '4th_alarm'],
                ['desc' => 'Electrical fire inside retail grocery store along national road.', 'cause' => 'Loose wire terminal connection on main distribution panel overheated and ignited plywood backing', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Bakery flour and cardboard packaging fire behind commercial oven.', 'cause' => 'Radiant heat from commercial brick oven ignited adjacent dry cardboard packaging stacks', 'alarm_level' => '3rd_alarm'],
                ['desc' => 'Motorcycle repair shop fire sparked during fuel tank draining.', 'cause' => 'Sparks from angle grinder ignited open pan of drained gasoline and solvent', 'alarm_level' => 'task_force_bravo'],
                ['desc' => 'Commercial beach resort pavilion electrical fire.', 'cause' => 'Moisture ingress into coastal electrical junction box caused phase-to-phase arcing', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Hardware store paint section fire.', 'cause' => 'Leaking aerosol lacquer spray punctured and ignited by electrical spark', 'alarm_level' => 'general_alarm'],
            ],
            'vehicular_fire' => [
                ['desc' => 'Passenger jeepney engine bay fire while travelling on highway.', 'cause' => 'Ruptured fuel line sprayed gasoline onto hot exhaust manifold', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Commercial delivery truck rear brake and tire fire.', 'cause' => 'Binding brake shoe overheated during steep descent, igniting dual rear tires', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Delivery van caught fire while idling along roadside.', 'cause' => 'Severe electrical short circuit in battery main cable', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Agricultural tractor fire during sugarcane harvesting.', 'cause' => 'Accumulated dry sugarcane trash around exhaust pipe ignited during heavy field operation', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Private passenger car engine fire in commercial parking lot.', 'cause' => 'Faulty aftermarket electrical amplifier wiring ignited engine cowl insulation', 'alarm_level' => '1st_alarm'],
            ],
            'storage_fire' => [
                ['desc' => 'Commercial bodega pallet and inventory storage fire.', 'cause' => 'High-intensity halogen flood lamp positioned too close to wooden shipping pallets', 'alarm_level' => '4th_alarm'],
                ['desc' => 'Agricultural feed and grain warehouse fire.', 'cause' => 'Spontaneous heating and combustion in damp stored copra and animal feeds', 'alarm_level' => '3rd_alarm'],
                ['desc' => 'Solvent and paint storage shed caught fire in commercial compound.', 'cause' => 'Vapor buildup in unventilated storage room ignited by electrical light switch spark', 'alarm_level' => 'task_force_charlie'],
                ['desc' => 'Dry lumber storage facility fire along municipal boundary.', 'cause' => 'Lightning strike ignited dry timber framing and stored dressed lumber', 'alarm_level' => '5th_alarm'],
            ],
            'rubbish_fire' => [
                ['desc' => 'Uncontrolled agricultural sugarcane field trash burning (kaingin) spreading rapidly.', 'cause' => 'Agricultural field clearing fire got out of hand due to sudden strong coastal wind gusts', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Dry grass and cogon wildfire spreading along highway road shoulder.', 'cause' => 'Lighted cigarette butt thrown out of passing vehicle into parched roadside brush', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Backyard leaf burning (siga) spread to boundary bamboo fence.', 'cause' => 'Unattended burning pile of dry mango leaves and twigs caught nearby timber structure', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Vacant lot rubbish fire threatening adjacent residential properties.', 'cause' => 'Spontaneous combustion of decomposing household waste and glass bottles under direct sunlight', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Brush fire spreading across vacant subdivision lots.', 'cause' => 'Open bonfire embers carried by wind into dry scrub vegetation', 'alarm_level' => '1st_alarm'],
            ],
            'others' => [
                ['desc' => 'Downed utility line sparking and burning on tree branches and zinc fence.', 'cause' => 'Tree branch fell onto 220V electric service lines, creating sustained electrical arc', 'alarm_level' => '1st_alarm'],
                ['desc' => 'Distribution transformer pole fire with leaking burning insulating oil.', 'cause' => 'Lightning-induced dielectric breakdown of transformer windings', 'alarm_level' => '2nd_alarm'],
                ['desc' => 'Electrical service drop cable catching fire along public street.', 'cause' => 'Overheated secondary distribution drop wire caused by illegal consumer taps', 'alarm_level' => '1st_alarm'],
            ],
        ];

        // Specific distribution across months of 2026 and 2025:
        // Month => [count, [incident_types]]
        // Peak dry season in March (Fire Prevention Month), April, May.
        // Moderate in Jan, Feb, Jun, Aug, Sep.
        $monthlySchedule = [
            // ── 2026 ──────────────────────────────────────────────────────────
            ['year' => 2026, 'month' => 9, 'count' => 6,  'b_dist' => [1, 2, 4, 6, 11, 15]],
            ['year' => 2026, 'month' => 8, 'count' => 8,  'b_dist' => [1, 3, 5, 8, 12, 13, 16, 18]],
            ['year' => 2026, 'month' => 7, 'count' => 7,  'b_dist' => [2, 6, 7, 9, 13, 14, 19]],
            ['year' => 2026, 'month' => 6, 'count' => 8,  'b_dist' => [1, 4, 8, 10, 12, 13, 15, 17]],
            ['year' => 2026, 'month' => 5, 'count' => 11, 'b_dist' => [1, 2, 3, 6, 8, 9, 11, 13, 14, 17, 18]],
            ['year' => 2026, 'month' => 4, 'count' => 13, 'b_dist' => [1, 2, 4, 5, 6, 7, 8, 10, 12, 13, 14, 16, 19]],
            ['year' => 2026, 'month' => 3, 'count' => 15, 'b_dist' => [1, 2, 3, 4, 6, 8, 9, 11, 12, 13, 14, 15, 17, 18, 19]], // Fire Prevention Month Peak
            ['year' => 2026, 'month' => 2, 'count' => 8,  'b_dist' => [1, 3, 6, 8, 10, 13, 14, 18]],
            ['year' => 2026, 'month' => 1, 'count' => 7,  'b_dist' => [1, 2, 5, 7, 12, 13, 15]],

            // ── 2025 (For trend year comparison on /analytics) ────────────────
            ['year' => 2025, 'month' => 12, 'count' => 5, 'b_dist' => [1, 2, 6, 12, 13]],
            ['year' => 2025, 'month' => 11, 'count' => 4, 'b_dist' => [3, 8, 13, 17]],
            ['year' => 2025, 'month' => 10, 'count' => 4, 'b_dist' => [1, 6, 9, 14]],
            ['year' => 2025, 'month' => 9,  'count' => 5, 'b_dist' => [2, 4, 8, 13, 18]],
            ['year' => 2025, 'month' => 8,  'count' => 4, 'b_dist' => [1, 5, 11, 15]],
            ['year' => 2025, 'month' => 7,  'count' => 4, 'b_dist' => [6, 10, 13, 16]],
            ['year' => 2025, 'month' => 6,  'count' => 5, 'b_dist' => [2, 7, 8, 12, 14]],
            ['year' => 2025, 'month' => 5,  'count' => 7, 'b_dist' => [1, 3, 6, 9, 13, 17, 18]],
            ['year' => 2025, 'month' => 4,  'count' => 8, 'b_dist' => [1, 2, 4, 6, 8, 13, 14, 19]],
            ['year' => 2025, 'month' => 3,  'count' => 11, 'b_dist' => [1, 2, 3, 5, 6, 8, 10, 12, 13, 15, 17]],
            ['year' => 2025, 'month' => 2,  'count' => 5, 'b_dist' => [1, 6, 8, 13, 18]],
            ['year' => 2025, 'month' => 1,  'count' => 4, 'b_dist' => [2, 9, 12, 14]],
        ];

        $reportIdCounter = 11;
        $typePool = [
            'residential_fire',
            'residential_fire',
            'residential_fire',
            'commercial_fire',
            'commercial_fire',
            'vehicular_fire',
            'vehicular_fire',
            'rubbish_fire',
            'rubbish_fire',
            'storage_fire',
            'others',
        ];

        foreach ($monthlySchedule as $schedule) {
            $year = $schedule['year'];
            $month = $schedule['month'];
            $count = $schedule['count'];
            $bDist = $schedule['b_dist'];

            for ($k = 0; $k < $count; $k++) {
                $barangayId = $bDist[$k % count($bDist)];
                $coords = $barangayCoordinates[$barangayId];

                // Pick a day within the month (earlier than today for Sept 2026)
                $maxDay = ($year === 2026 && $month === 9) ? 20 : Carbon::create($year, $month, 1)->daysInMonth;
                $day = min(1 + (int) floor(($k + 1) * ($maxDay / ($count + 1))), $maxDay);
                $hour = 7 + (($k * 3) % 15);
                $minute = 5 + (($k * 7) % 50);

                $incidentTime = Carbon::create($year, $month, $day, $hour, $minute, 0);

                // Select incident type and scenario
                $type = $typePool[($reportIdCounter + $k) % count($typePool)];
                $typeScenarios = $scenarios[$type];
                $scenario = $typeScenarios[($reportIdCounter + $k) % count($typeScenarios)];

                // Jitter coordinate realistically around the barangay center (~200 to 500 meters)
                $latOffset = ((($k * 13) % 40) - 20) / 10000;
                $lngOffset = ((($k * 17) % 40) - 20) / 10000;
                $latitude = round($coords['lat'] + $latOffset, 7);
                $longitude = round($coords['lng'] + $lngOffset, 7);

                // Resident reporter
                $reporter = $residents[$reportIdCounter % count($residents)];

                // Responder assignments
                $verifier = $bfpResponders[($reportIdCounter) % count($bfpResponders)];
                $commander = $bfpResponders[($reportIdCounter + 2) % count($bfpResponders)];
                $investigator = $bfpResponders[($reportIdCounter + 4) % count($bfpResponders)];

                $aiConf = round(0.8800 + (($reportIdCounter * 3) % 110) / 10000, 4);

                // Create resolved CommunityReport
                $report = CommunityReport::create([
                    'report_id' => $reportIdCounter,
                    'user_id' => $reporter->id,
                    'reporter_name' => $reporter->name,
                    'contact_number' => $reporter->contact_number,
                    'description' => $scenario['desc'],
                    'report_image' => ($k % 3 === 0) ? 'report_images/report_8.jpg' : null,
                    'ai_fire_label' => ($type === 'rubbish_fire' && $k % 2 === 0) ? 'smoke' : 'fire',
                    'ai_fire_confidence' => $aiConf,
                    'ai_verified_at' => $incidentTime->copy()->addMinutes(2),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'barangay_id' => $barangayId,
                    'status' => CommunityReport::STATUS_RESOLVED,
                    'created_at' => $incidentTime,
                    'updated_at' => $incidentTime->copy()->addMinutes(45 + ($k % 30)),
                ]);

                // Status history chain: Pending -> Accepted -> Dispatched -> Resolved
                ReportStatusHistory::create([
                    'report_id' => $report->report_id,
                    'status' => CommunityReport::STATUS_PENDING,
                    'notes' => 'Incident reported via FireSight citizen application with GPS location.',
                    'changed_by' => $reporter->id,
                    'created_at' => $report->created_at,
                ]);

                ReportStatusHistory::create([
                    'report_id' => $report->report_id,
                    'status' => CommunityReport::STATUS_ACCEPTED,
                    'notes' => "Verified by duty verifier {$verifier->name} ({$verifier->personnelDetails?->rank}). Priority response dispatched.",
                    'changed_by' => $verifier->id,
                    'created_at' => $report->created_at->copy()->addMinutes(4),
                ]);

                ReportStatusHistory::create([
                    'report_id' => $report->report_id,
                    'status' => CommunityReport::STATUS_DISPATCHED,
                    'notes' => "Engine unit deployed from Lian Station under ground commander {$commander->name}.",
                    'changed_by' => $commander->id,
                    'created_at' => $report->created_at->copy()->addMinutes(9),
                ]);

                ReportStatusHistory::create([
                    'report_id' => $report->report_id,
                    'status' => CommunityReport::STATUS_RESOLVED,
                    'notes' => "Fire controlled and declared out. Post-incident assessment completed by {$investigator->name}.",
                    'changed_by' => $investigator->id,
                    'created_at' => $report->updated_at,
                ]);

                // IncidentRecord
                $casualties = (in_array($scenario['alarm_level'], AlarmLevel::criticalValues(), true) && $k % 4 === 0) ? 1 : 0;
                IncidentRecord::create([
                    'report_id' => $report->report_id,
                    'barangay_id' => $barangayId,
                    'incident_datetime' => $report->updated_at,
                    'incident_type' => $type,
                    'alarm_level' => $scenario['alarm_level'],
                    'cause_of_fire' => $scenario['cause'],
                    'casualties' => $casualties,
                    'notes' => "Incident declared completely out after {$report->created_at->diffInMinutes($report->updated_at)} minutes of operations. Property damage assessment filed.",
                ]);

                $reportIdCounter++;
            }
        }
    }
}
