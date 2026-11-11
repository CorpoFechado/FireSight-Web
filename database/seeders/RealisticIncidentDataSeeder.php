<?php

namespace Database\Seeders;

use App\Support\Geo;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RealisticIncidentDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * 2 full years (24 months) of realistic incident volumes for Lian, Batangas.
     * Enriched past history with distinct seasonal peaks (March-June dry months).
     * Year 1 (months -23 to -12): 49 incidents
     * Year 2 (months -11 to 0):   51 incidents
     * Total = 100 incidents across 2 years.
     *
     * @var list<int>
     */
    private const MONTHLY_VOLUMES = [
        // Year 1 (2025)
        3, 3, 4, 6, 8, 7, 4, 3, 2, 2, 3, 4,
        // Year 2 (2026 - current)
        3, 3, 4, 7, 9, 8, 4, 3, 2, 2, 4, 2,
    ];

    /** @var list<int> */
    private const REPORTING_HOURS = [7, 8, 10, 11, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22];

    /**
     * The 3 verified community report photos available on the backend.
     *
     * @var list<string>
     */
    private const REPORT_IMAGES = [
        'uploads/reports/report_44_1793446882_aca83633.jpg',
        'uploads/reports/report_44_1794347202_72f2e635.jpg',
        'uploads/reports/report_44_1793496551_6580dd19.jpg',
    ];

    /**
     * Standard fire trucks stationed at BFP Lian.
     *
     * @var list<string>
     */
    private const TRUCKS = [
        'Fire Truck 1',
        'Fire Truck 2',
        'Fire Truck 3',
    ];

    /** @var list<string> */
    private const WALK_IN_NAMES = [
        'Aling Nena Santos', 'Mang Tony Reyes', 'Carlo Mendoza', 'Jessa Mae Cruz',
        'Ramon Villanueva', 'Liza Bautista', 'Paolo Garcia', 'Marites Navarro',
        'Mang Lito Hernandez', 'Aling Tessie Dimaculangan', 'Vicente Castillo', 'Lorna Dalisay',
    ];

    /**
     * Categorized, realistic fire scenarios with short, authentic causes and damages.
     *
     * @var array<string, list<array{desc: string, cause: string, est_min: int, est_max: int}>>
     */
    private const SCENARIOS = [
        'residential_fire' => [
            ['desc' => 'May apoy at makapal na usok na lumalabas sa kusina ng bahay; mabilis kumalat sa kisame.', 'cause' => 'Electrical short circuit', 'est_min' => 120000, 'est_max' => 450000],
            ['desc' => 'Flames visible from the second-floor bedroom of a residential light-material house.', 'cause' => 'Overloaded power strip', 'est_min' => 150000, 'est_max' => 550000],
            ['desc' => 'Naamoy ang tumatagas na LPG bago biglang nagliyab ang lutuan sa kusina.', 'cause' => 'LPG stove hose leak', 'est_min' => 80000, 'est_max' => 300000],
            ['desc' => 'A candle left during brownout ignited curtains and a nearby wooden wall.', 'cause' => 'Unattended candle', 'est_min' => 100000, 'est_max' => 400000],
            ['desc' => 'Naiwang nakasindi ang katol malapit sa kurtina at kumalat sa dingding.', 'cause' => 'Katol ember ignition', 'est_min' => 70000, 'est_max' => 250000],
            ['desc' => 'Naiwang nakasalang na kawali sa kalan habang nagluluto, umapoy sa cabinet.', 'cause' => 'Unattended cooking', 'est_min' => 90000, 'est_max' => 350000],
            ['desc' => 'Umusok ang lumang electric fan at nagliyab ang plastic casing nito sa kwarto.', 'cause' => 'Appliance motor burnout', 'est_min' => 60000, 'est_max' => 220000],
        ],
        'rubbish_fire' => [
            ['desc' => 'Sinisiga ang tuyong dahon sa bakanteng lote at kumalat ang apoy dahil sa malakas na hangin.', 'cause' => 'Open trash burning', 'est_min' => 2000, 'est_max' => 15000],
            ['desc' => 'Dry grass and roadside rubbish are burning close to residential perimeter fences.', 'cause' => 'Discarded cigarette butt', 'est_min' => 3000, 'est_max' => 20000],
            ['desc' => 'May makapal na usok mula sa tambak na tuyong basura sa likod ng covered court.', 'cause' => 'Open waste burning', 'est_min' => 2500, 'est_max' => 12000],
            ['desc' => 'A small brush fire is spreading through cogon grass near a sugarcane boundary.', 'cause' => 'Agricultural waste burning', 'est_min' => 5000, 'est_max' => 25000],
            ['desc' => 'Nag-apoy ang tuyong damo sa gilid ng pilapil dahil sa naiwang sigarilyo.', 'cause' => 'Grassland ember ignition', 'est_min' => 4000, 'est_max' => 18000],
            ['desc' => 'Makapal na usok mula sa sinunog na dayami sa bukid na kumakalat sa kalsada.', 'cause' => 'Sugarcane field stubble fire', 'est_min' => 6000, 'est_max' => 30000],
        ],
        'commercial_fire' => [
            ['desc' => 'Heavy smoke coming from the kitchen exhaust of a commercial eatery.', 'cause' => 'Kitchen exhaust duct grease', 'est_min' => 250000, 'est_max' => 850000],
            ['desc' => 'Nagliyab ang electrical panel board sa loob ng sari-sari store malapit sa palengke.', 'cause' => 'Panel board overload', 'est_min' => 180000, 'est_max' => 650000],
            ['desc' => 'Cardboard packaging inventory behind a bakery oven caught fire and spread to shelves.', 'cause' => 'Bakery oven ember', 'est_min' => 300000, 'est_max' => 950000],
            ['desc' => 'Nagliyab ang kusina ng resort cottage dahil sa tumagas na tangke ng gasul.', 'cause' => 'Resort kitchen gas leak', 'est_min' => 350000, 'est_max' => 1100000],
        ],
        'vehicular_fire' => [
            ['desc' => 'Nasusunog ang engine bay ng delivery van sa gilid ng highway; nakalabas ang sakay.', 'cause' => 'Engine wiring short circuit', 'est_min' => 80000, 'est_max' => 300000],
            ['desc' => 'A passenger jeepney rear tire and brake assembly caught fire while travelling.', 'cause' => 'Overheated brake lining', 'est_min' => 45000, 'est_max' => 180000],
            ['desc' => 'Sparks and fire appeared underneath a parked motorcycle after starting.', 'cause' => 'Fuel line leak', 'est_min' => 30000, 'est_max' => 100000],
            ['desc' => 'Nag-overheat ang radiator at nagliyab ang baterya ng isang pampasaherong tricycle.', 'cause' => 'Battery electrical fault', 'est_min' => 25000, 'est_max' => 90000],
        ],
        'storage_fire' => [
            ['desc' => 'Bodega containing dried copra and sacks has visible flames and heavy smoke at the rear.', 'cause' => 'Spontaneous copra heating', 'est_min' => 400000, 'est_max' => 1200000],
            ['desc' => 'A small agricultural equipment shed caught fire from defective circuit wiring.', 'cause' => 'Defective warehouse wiring', 'est_min' => 150000, 'est_max' => 600000],
        ],
        'others' => [
            ['desc' => 'A downed electrical service drop wire sparked and ignited a wooden fence and tree.', 'cause' => 'Fallen service drop wire', 'est_min' => 15000, 'est_max' => 60000],
            ['desc' => 'Nagliyab ang transformer sa poste malapit sa kanto at may tumulong mainit na langis.', 'cause' => 'Transformer dielectric breakdown', 'est_min' => 35000, 'est_max' => 120000],
        ],
    ];

    /**
     * Exactly 4 false alarm reports across the full 2-year period (< 5).
     *
     * @var list<string>
     */
    private const INVALID_DESCRIPTIONS = [
        'Akala namin sunog, makapal na usok lang pala mula sa nag-iihaw ng pagkain sa bakuran.',
        'Reported orange glow was confirmed as controlled welding repair work at night.',
        'Singaw mula sa palayan at alikabok na may reflection ang napagkamalang usok.',
        'Mausok na tambutso ng lumang traktora ang napagkamalang sumisiklab na sunog.',
    ];

    /**
     * Standard street / purok names in Lian, Batangas.
     *
     * @var list<string>
     */
    private const LIAN_STREETS = [
        'J.P. Rizal St., Purok 1',
        'M.H. Del Pilar St., Purok 2',
        'A. Bonifacio St., Purok 3',
        'Zobel Highway, Sitio Centro',
        'National Road, Sitio Aplaya',
        'Purok Ilaya, Block 2',
        'Purok Ibaba, Sitio Crossing',
        'Purok Maligaya, Lot 5',
        'Sitio Riverside, Purok 4',
        'Sitio Balateros, Purok 1',
        'Seaside Drive, Sitio Kanluran',
        'Purok Silangan, Block 1',
        'Sitio Central, Purok 5',
        'Purok Sampaguita',
        'Sitio Bukid, Purok 2',
    ];

    public function run(): void
    {
        $residents = DB::table('users')->where('role', 'resident')->orderBy('user_id')->get();
        $personnel = DB::table('users')
            ->where('role', 'bfp_personnel')
            ->where('status', 'active')
            ->orderBy('user_id')
            ->pluck('user_id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
        $adminId = DB::table('users')
            ->where('role', 'bfp_admin')
            ->where('status', 'active')
            ->orderBy('user_id')
            ->value('user_id');

        $barangays = DB::table('barangay')->orderBy('barangay_id')->get();

        if ($residents->isEmpty() || $personnel === [] || $barangays->isEmpty()) {
            throw new RuntimeException('RealisticIncidentDataSeeder requires existing residents, BFP personnel, and barangays.');
        }

        if (DB::table('afor_report')->count() > 0) {
            throw new RuntimeException('Cannot rebuild incident data while out-of-scope afor_report rows reference incident_record.');
        }

        $now = Date::now();

        DB::transaction(function () use ($adminId, $barangays, $now, $personnel, $residents): void {
            $this->clearScopedTables();
            $this->seedResidentAddresses($residents, $barangays);
            $this->seedReports($residents, $personnel, $adminId, $barangays, $now);
        });

        // Automatically sync matching notifications for all active and recent reports
        $this->call(AdminNotificationSeeder::class);
    }

    private function clearScopedTables(): void
    {
        DB::table('report_link')->delete();
        DB::table('report_evidence')->delete();
        DB::table('incident_dispatch_personnel')->delete();
        DB::table('incident_dispatch_truck')->delete();
        DB::table('incident_dispatch')->delete();
        DB::table('report_status_history')->delete();
        DB::table('incident_record')->delete();
        DB::table('community_report')->delete();
        DB::table('resident_address')->delete();
    }

    /**
     * Ensures all resident users are active and assigned an address in Lian.
     *
     * @param  Collection<int, object>  $residents
     * @param  Collection<int, object>  $barangays
     */
    private function seedResidentAddresses(Collection $residents, Collection $barangays): void
    {
        $bCount = $barangays->count();
        $sCount = count(self::LIAN_STREETS);

        foreach ($residents as $index => $resident) {
            DB::table('users')
                ->where('user_id', $resident->user_id)
                ->update([
                    'status' => 'active',
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]);

            $assignedBarangay = $barangays[$index % $bCount];
            $street = self::LIAN_STREETS[$index % $sCount];

            DB::table('resident_address')->insert([
                'user_id' => $resident->user_id,
                'house_no_street' => $street,
                'barangay_id' => $assignedBarangay->barangay_id,
                'municipality' => 'Lian',
                'province' => 'Batangas',
            ]);
        }
    }

    /**
     * Seeds 2 full years of authentic reports, official incident records, and normalized dispatches.
     *
     * @param  Collection<int, object>  $residents
     * @param  list<int>  $personnel
     * @param  Collection<int, object>  $barangays
     */
    private function seedReports(
        Collection $residents,
        array $personnel,
        ?int $adminId,
        Collection $barangays,
        CarbonInterface $now,
    ): void {
        $barangayPool = $this->weightedBarangayPool($barangays);
        $globalIndex = 0;
        $imageCount = count(self::REPORT_IMAGES);

        // Dispatches are strictly handled by BFP Personnel (never the Admin)
        $dispatcherIds = array_slice($personnel, 0, min(3, count($personnel)));
        $fieldResponders = array_slice($personnel, min(3, count($personnel)));

        $numMonths = count(self::MONTHLY_VOLUMES);
        $lastResolvedReportId = null;

        foreach (self::MONTHLY_VOLUMES as $monthIndex => $volume) {
            $monthsAgo = $numMonths - 1 - $monthIndex;
            $monthStart = $now->copy()->subMonthsNoOverflow($monthsAgo)->startOfMonth();

            for ($monthlyIndex = 0; $monthlyIndex < $volume; $monthlyIndex++, $globalIndex++) {
                // Exactly 4 invalid reports across 24 months (< 5 total)
                $isInvalid = ($monthsAgo === 21 && $monthlyIndex === 1)
                    || ($monthsAgo === 15 && $monthlyIndex === 2)
                    || ($monthsAgo === 9 && $monthlyIndex === 1)
                    || ($monthsAgo === 3 && $monthlyIndex === 1);

                // Current month: 1 active 'dispatched' report (happening now), 1 'resolved' report (6 days ago). 0 resolved today!
                $isCurrentMonth = ($monthsAgo === 0);
                if ($isCurrentMonth) {
                    $status = ($monthlyIndex === 1) ? 'dispatched' : 'resolved';
                } elseif ($isInvalid) {
                    $status = 'invalid';
                } else {
                    $status = 'resolved';
                }

                $isActive = ($status === 'dispatched');

                // Timestamp distribution
                if ($isActive) {
                    $createdAt = $now->copy()->subMinutes(30);
                } elseif ($isCurrentMonth) {
                    // Resolved 6 days ago (0 resolved today)
                    $createdAt = $now->copy()->subDays(6)->setHour(14)->setMinute(20);
                } else {
                    $reportingHour = self::REPORTING_HOURS[($globalIndex * 7) % count(self::REPORTING_HOURS)];
                    $dayOffset = 1 + (int) floor(($monthlyIndex * 27) / max(1, $volume)) + ($globalIndex % 3);
                    $createdAt = $monthStart->copy()->addDays(min(27, $dayOffset))->setHour($reportingHour)->setMinute(($globalIndex * 11) % 60);
                }

                // In peak dry months (March, April, May, June), weight heavily toward Binubusan, Bungahan, Matabungkay, Prenza
                $isDryPeakMonth = in_array($monthStart->month, [3, 4, 5, 6], true);
                if ($isDryPeakMonth && $globalIndex % 2 === 0) {
                    $hotBarangayIds = [8, 9, 16, 17, 19]; // Binubusan, Bungahan, Matabungkay, Prenza, San Diego
                    $chosenId = $hotBarangayIds[$globalIndex % count($hotBarangayIds)];
                    $barangay = $barangays->firstWhere('barangay_id', $chosenId) ?? $barangayPool[$globalIndex % count($barangayPool)];
                } else {
                    $resident = $residents[$globalIndex % $residents->count()];
                    $residentAddress = DB::table('resident_address')->where('user_id', $resident->user_id)->first();
                    $barangay = $barangays->firstWhere('barangay_id', $residentAddress?->barangay_id)
                        ?? $barangayPool[$globalIndex % count($barangayPool)];
                }

                $incidentType = $this->incidentTypeFor($globalIndex, $isDryPeakMonth);
                $scenario = $this->scenarioFor($incidentType, $globalIndex);

                // Alarm levels: ONLY 1st_alarm (majority) or 2nd_alarm (ceiling limit!)
                $alarmLevel = ($incidentType === 'residential_fire' && $globalIndex % 8 === 0)
                    || ($incidentType === 'commercial_fire' && $globalIndex % 5 === 0)
                    ? '2nd_alarm'
                    : '1st_alarm';

                $coordinates = $this->pointWithinBarangay($barangay, $globalIndex + 1);
                [$deviceLat, $deviceLng, $accuracy] = $this->deviceCoordinates($coordinates[0], $coordinates[1], $globalIndex);

                // AI Detection: Invalid reports get "no_fire" (< 0.45); real fires get "fire" (high confidence)
                if ($isInvalid) {
                    $aiLabel = 'no_fire';
                    $aiConfidence = round(0.24 + ($globalIndex % 15) * 0.01, 4); // 0.24 - 0.38
                    $description = self::INVALID_DESCRIPTIONS[$globalIndex % count(self::INVALID_DESCRIPTIONS)];
                    $reportImage = null;
                } else {
                    $aiLabel = 'fire';
                    $aiConfidence = round(0.84 + ($globalIndex % 13) * 0.01, 4); // 0.84 - 0.96
                    $description = $scenario['desc'];
                    $reportImage = self::REPORT_IMAGES[$globalIndex % $imageCount];
                }

                $timeline = $this->timelineFor($status, $createdAt, $alarmLevel, $personnel, $adminId, $globalIndex);
                $updatedAt = $timeline[array_key_last($timeline)]['created_at'];

                $reportingUser = $residents[$globalIndex % $residents->count()];

                $reportId = (int) DB::table('community_report')->insertGetId([
                    'user_id' => $reportingUser->user_id,
                    'reporter_name' => trim($reportingUser->first_name.' '.$reportingUser->last_name),
                    'contact_number' => $reportingUser->contact_number ?? sprintf('09%09d', 170000000 + (($globalIndex * 7919) % 89999999)),
                    'description' => $description,
                    'report_image' => $reportImage,
                    'ai_fire_label' => $aiLabel,
                    'ai_fire_confidence' => $aiConfidence,
                    'ai_verified_at' => $createdAt->copy()->addMinutes(1 + ($globalIndex % 3)),
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'barangay_id' => $barangay->barangay_id,
                    'location_accuracy_m' => $accuracy,
                    'device_latitude' => $deviceLat,
                    'device_longitude' => $deviceLng,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                // Insert Status History
                DB::table('report_status_history')->insert(array_map(
                    fn (array $h): array => ['report_id' => $reportId, ...$h],
                    $timeline,
                ));

                // Dispatches occur for both active dispatched reports and resolved reports
                if (! $isInvalid) {
                    $this->seedDispatchesForReport(
                        $reportId,
                        $createdAt,
                        $incidentType,
                        $alarmLevel,
                        $dispatcherIds,
                        $fieldResponders,
                        $globalIndex,
                    );
                }

                // RULE: ONLY 'resolved' community reports enter incident_record as official records!
                // If status is pending, accepted, or dispatched, NO incident_record is created!
                if ($status === 'resolved') {
                    $injuries = ($incidentType === 'residential_fire' && $globalIndex % 11 === 0) ? 1 : 0;
                    $fatalities = 0;
                    $damage = (float) ($scenario['est_min'] + ($globalIndex * 1791) % ($scenario['est_max'] - $scenario['est_min']));
                    if ($alarmLevel === '2nd_alarm') {
                        $damage *= 1.75;
                    }

                    $fireStarted = $createdAt->copy()->subMinutes(6 + ($globalIndex % 8));
                    $fireOut = $createdAt->copy()->addMinutes(35 + ($globalIndex % 30));

                    $establishmentName = match ($incidentType) {
                        'commercial_fire' => 'Lian Commercial Eatery & Groceries',
                        'storage_fire' => 'Lian Copra & Grain Warehouse',
                        default => null,
                    };

                    DB::table('incident_record')->insert([
                        'report_id' => $reportId,
                        'barangay_id' => $barangay->barangay_id,
                        'incident_datetime' => $createdAt->copy()->subMinutes(2),
                        'incident_type' => $incidentType,
                        'alarm_level' => $alarmLevel,
                        'cause_of_fire' => $scenario['cause'],
                        'casualties' => $injuries + $fatalities,
                        'injuries' => $injuries,
                        'fatalities' => $fatalities,
                        'time_fire_started' => $fireStarted,
                        'time_fire_out' => $fireOut,
                        'establishment_name' => $establishmentName,
                        'owner_name' => self::WALK_IN_NAMES[$globalIndex % count(self::WALK_IN_NAMES)],
                        'occupant_name' => $reportingUser->first_name.' '.$reportingUser->last_name,
                        'estimated_damage' => round($damage, 2),
                        'notes' => 'Scene secured after fire suppression and overhaul. Initial investigation completed.',
                    ]);

                    // Occasional duplicate link
                    if ($globalIndex === 35 && $lastResolvedReportId !== null) {
                        DB::table('report_link')->insert([
                            'main_report_id' => $lastResolvedReportId,
                            'related_report_id' => $reportId,
                        ]);
                    }
                    $lastResolvedReportId = $reportId;
                }
            }
        }
    }

    /**
     * Seeds normalized dispatch records, fire trucks, and personnel.
     * Uses trucks: 'Fire Truck 1', 'Fire Truck 2', 'Fire Truck 3'.
     *
     * Enforces the rule:
     * If a personnel or truck was deployed in the 1st dispatch,
     * they CANNOT be re-deployed in the 2nd dispatch for the same report.
     *
     * @param  list<int>  $dispatcherIds
     * @param  list<int>  $fieldResponders
     */
    private function seedDispatchesForReport(
        int $reportId,
        CarbonInterface $createdAt,
        string $incidentType,
        string $alarmLevel,
        array $dispatcherIds,
        array $fieldResponders,
        int $seed,
    ): void {
        $dispatcher = $dispatcherIds[$seed % count($dispatcherIds)];
        $firstDispatchedAt = $createdAt->copy()->addMinutes(3);

        // Partition trucks:
        // 1st Dispatch uses Fire Truck 1 (or Fire Truck 1 & 2)
        // 2nd Dispatch uses Fire Truck 3 (NEVER overlapping)
        $firstTrucks = match ($incidentType) {
            'rubbish_fire' => [self::TRUCKS[0]], // Fire Truck 1
            'vehicular_fire' => [self::TRUCKS[0]], // Fire Truck 1
            default => ($alarmLevel === '2nd_alarm')
                ? [self::TRUCKS[0]] // Fire Truck 1 for initial
                : [self::TRUCKS[0], self::TRUCKS[1]], // Fire Truck 1 & 2 for 1st alarm structure
        };

        // Partition personnel into 2 non-overlapping pools
        $halfCount = (int) ceil(count($fieldResponders) / 2);
        $firstCrewPool = array_slice($fieldResponders, 0, $halfCount);
        $secondCrewPool = array_slice($fieldResponders, $halfCount);

        $firstCrew = array_slice($firstCrewPool, 0, min(3, count($firstCrewPool)));

        // 1st Dispatch
        $d1Id = (int) DB::table('incident_dispatch')->insertGetId([
            'report_id' => $reportId,
            'dispatched_by' => $dispatcher,
            'dispatched_at' => $firstDispatchedAt,
        ]);

        foreach ($firstTrucks as $tName) {
            DB::table('incident_dispatch_truck')->insert([
                'dispatch_id' => $d1Id,
                'truck_name' => $tName,
            ]);
        }

        foreach ($firstCrew as $userId) {
            DB::table('incident_dispatch_personnel')->insert([
                'dispatch_id' => $d1Id,
                'user_id' => $userId,
            ]);
        }

        // 2nd Alarm Escalation Dispatch: Uses COMPLETELY DIFFERENT truck and personnel
        if ($alarmLevel === '2nd_alarm') {
            $secondDispatchedAt = $firstDispatchedAt->copy()->addMinutes(14);
            $secondTrucks = [self::TRUCKS[2]]; // Fire Truck 3 (not in 1st dispatch!)
            $secondCrew = array_slice($secondCrewPool, 0, min(3, count($secondCrewPool))); // Crew not in 1st dispatch!

            $d2Id = (int) DB::table('incident_dispatch')->insertGetId([
                'report_id' => $reportId,
                'dispatched_by' => $dispatcher,
                'dispatched_at' => $secondDispatchedAt,
            ]);

            foreach ($secondTrucks as $tName) {
                DB::table('incident_dispatch_truck')->insert([
                    'dispatch_id' => $d2Id,
                    'truck_name' => $tName,
                ]);
            }

            foreach ($secondCrew as $userId) {
                DB::table('incident_dispatch_personnel')->insert([
                    'dispatch_id' => $d2Id,
                    'user_id' => $userId,
                ]);
            }
        }
    }

    /**
     * Builds realistic status transition timeline.
     * Enforces the rule: Accepting and Dispatching are STRICTLY performed by BFP Personnel.
     * The Admin NEVER accepts or dispatches, but can occasionally resolve incidents (~15% of the time).
     *
     * @param  list<int>  $personnel
     * @return list<array{status: string, notes: string, changed_by: int|null, created_at: CarbonInterface}>
     */
    private function timelineFor(
        string $status,
        CarbonInterface $createdAt,
        string $alarmLevel,
        array $personnel,
        ?int $adminId,
        int $index,
    ): array {
        $pCount = count($personnel);

        $timeline = [[
            'status' => 'pending',
            'notes' => 'Initial community report received via mobile app.',
            'changed_by' => null,
            'created_at' => $createdAt,
        ]];

        if ($status === 'pending') {
            return $timeline;
        }

        if ($status === 'invalid') {
            $closedBy = ($adminId !== null && $index % 2 === 0)
                ? $adminId
                : $personnel[($index + 1) % $pCount];

            $timeline[] = [
                'status' => 'invalid',
                'notes' => 'Verified on scene as non-fire false alarm; report closed.',
                'changed_by' => $closedBy,
                'created_at' => $createdAt->copy()->addMinutes(8),
            ];

            return $timeline;
        }

        // Accepted: STRICTLY by BFP Personnel (never the Admin)
        $acceptedAt = $createdAt->copy()->addMinutes(2);
        $timeline[] = [
            'status' => 'accepted',
            'notes' => 'Fire report verified and confirmed; response activated.',
            'changed_by' => $personnel[($index + 1) % $pCount],
            'created_at' => $acceptedAt,
        ];

        // Dispatched: STRICTLY by BFP Personnel (never the Admin)
        $dispatchedAt = $acceptedAt->copy()->addMinutes(2);
        $timeline[] = [
            'status' => 'dispatched',
            'notes' => 'BFP Lian fire apparatus and duty crew dispatched to the scene.',
            'changed_by' => $personnel[($index + 2) % $pCount],
            'created_at' => $dispatchedAt,
        ];

        if ($status === 'dispatched') {
            return $timeline;
        }

        // Resolved: Mostly (~85%) by field personnel completing overhaul on scene;
        // Occasionally (~15%) closed administratively by the Web Admin.
        $resolvedBy = ($adminId !== null && $index % 7 === 0)
            ? $adminId
            : $personnel[($index + 3) % $pCount];

        $resolutionMinutes = ($alarmLevel === '2nd_alarm') ? 70 : 40;
        $timeline[] = [
            'status' => 'resolved',
            'notes' => ($resolvedBy === $adminId)
                ? 'Incident closed and finalized in administrative spot report records.'
                : 'Fire completely suppressed, overhaul completed, and area secured.',
            'changed_by' => $resolvedBy,
            'created_at' => $dispatchedAt->copy()->addMinutes($resolutionMinutes),
        ];

        return $timeline;
    }

    /**
     * Weights barangays based on actual density, commercial activity, and vegetation risk factors in Lian.
     *
     * @param  Collection<int, object>  $barangays
     * @return list<object>
     */
    private function weightedBarangayPool(Collection $barangays): array
    {
        $weights = [
            1 => 4,  // Barangay 1 (Poblacion)
            2 => 4,  // Barangay 2 (Poblacion)
            3 => 3,  // Barangay 3 (Poblacion)
            4 => 3,  // Barangay 4 (Poblacion)
            5 => 3,  // Barangay 5 (Poblacion)
            8 => 5,  // Binubusan (Extensive Sugarcane & Grasslands)
            9 => 4,  // Bungahan (Sugarcane / Open burning)
            14 => 3, // Luyahan (Farmlands)
            16 => 6, // Matabungkay (Beach Resorts, Bamboo Cottages, Grills, Tourism)
            17 => 4, // Prenza (Open Fields & Pasture)
            19 => 3, // San Diego (Rice & Agricultural)
        ];

        $pool = [];
        foreach ($barangays as $b) {
            $w = $weights[$b->barangay_id] ?? 2;
            for ($i = 0; $i < $w; $i++) {
                $pool[] = $b;
            }
        }

        return $pool;
    }

    private function incidentTypeFor(int $index, bool $isDryPeakMonth = false): string
    {
        if ($isDryPeakMonth) {
            $types = [
                'rubbish_fire',
                'rubbish_fire',
                'residential_fire',
                'rubbish_fire',
                'commercial_fire',
                'rubbish_fire',
                'residential_fire',
                'vehicular_fire',
                'rubbish_fire',
                'storage_fire',
            ];
        } else {
            $types = [
                'residential_fire',
                'rubbish_fire',
                'residential_fire',
                'commercial_fire',
                'residential_fire',
                'vehicular_fire',
                'rubbish_fire',
                'residential_fire',
                'storage_fire',
                'others',
            ];
        }

        return $types[$index % count($types)];
    }

    /**
     * @return array{desc: string, cause: string, est_min: int, est_max: int}
     */
    private function scenarioFor(string $type, int $index): array
    {
        $list = self::SCENARIOS[$type] ?? self::SCENARIOS['others'];

        return $list[$index % count($list)];
    }

    /** @return array{0: float, 1: float} */
    private function pointWithinBarangay(object $barangay, int $seed): array
    {
        $boundary = json_decode((string) ($barangay->boundary_geojson ?? ''), true);
        $ring = $this->boundaryRing($boundary);

        if ($ring === []) {
            return [(float) $barangay->centroid_lat, (float) $barangay->centroid_lng];
        }

        $longitudes = array_column($ring, 0);
        $latitudes = array_column($ring, 1);
        $minLng = min($longitudes);
        $maxLng = max($longitudes);
        $minLat = min($latitudes);
        $maxLat = max($latitudes);

        for ($attempt = 0; $attempt < 300; $attempt++) {
            $lat = $minLat + ($maxLat - $minLat) * $this->fraction($seed, $attempt * 2 + 7);
            $lng = $minLng + ($maxLng - $minLng) * $this->fraction($seed, $attempt * 2 + 8);

            if (Geo::pointInPolygon($lat, $lng, $ring)) {
                return [round($lat, 8), round($lng, 8)];
            }
        }

        return [round((float) $ring[0][1], 8), round((float) $ring[0][0], 8)];
    }

    /**
     * @return array<int, array{0: float|int, 1: float|int}>
     */
    private function boundaryRing(mixed $boundary): array
    {
        if (! is_array($boundary) || $boundary === []) {
            return [];
        }

        if (isset($boundary['type'], $boundary['coordinates'])) {
            return $boundary['type'] === 'MultiPolygon'
                ? ($boundary['coordinates'][0][0] ?? [])
                : ($boundary['coordinates'][0] ?? []);
        }

        return $boundary;
    }

    /** @return array{0: float, 1: float, 2: int} */
    private function deviceCoordinates(float $latitude, float $longitude, int $seed): array
    {
        $distanceMeters = 5 + ($seed % 20);
        $bearing = deg2rad(($seed * 47) % 360);
        $latitudeOffset = cos($bearing) * $distanceMeters / 111320;
        $longitudeScale = max(0.1, cos(deg2rad($latitude)));
        $longitudeOffset = sin($bearing) * $distanceMeters / (111320 * $longitudeScale);

        return [
            round($latitude + $latitudeOffset, 8),
            round($longitude + $longitudeOffset, 8),
            5 + ($seed * 7) % 35,
        ];
    }

    private function fraction(int $seed, int $salt): float
    {
        return hexdec(substr(hash('sha256', $seed.'-'.$salt), 0, 8)) / 4294967295;
    }
}
