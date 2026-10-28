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

    /** @var list<int> */
    private const MONTHLY_VOLUMES = [14, 22, 20, 16, 70, 60, 55, 20, 12, 11, 18, 67];

    /** @var list<int> */
    private const REPORTING_HOURS = [1, 6, 8, 10, 11, 13, 14, 14, 15, 15, 16, 16, 17, 17, 18, 18, 19, 20, 21, 23];

    /** @var list<string> */
    private const WALK_IN_NAMES = [
        'Aling Nena Santos', 'Mang Tony Reyes', 'Carlo Mendoza', 'Jessa Mae Cruz',
        'Ramon Villanueva', 'Liza Bautista', 'Paolo Garcia', 'Marites Navarro',
    ];

    /** @var array<string, list<array{description: string, cause: string}>> */
    private const SCENARIOS = [
        'residential_fire' => [
            ['description' => 'May apoy at makapal na usok na lumalabas sa kusina ng bahay; mabilis kumalat sa kisame.', 'cause' => 'Unattended cooking oil ignited nearby cabinets'],
            ['description' => 'Flames are visible from the second-floor bedroom of a light-material house.', 'cause' => 'Faulty electrical wiring inside the bedroom ceiling'],
            ['description' => 'Naamoy ang tumatagas na LPG bago biglang nagliyab ang kusina ng bahay.', 'cause' => 'LPG hose leak ignited by the stove flame'],
            ['description' => 'A candle left during a brownout ignited curtains and a wooden wall.', 'cause' => 'Unattended candle during a power interruption'],
            ['description' => 'Nag-overheat ang extension cord at nasunog ang sofa sa sala.', 'cause' => 'Overloaded extension outlet beside upholstered furniture'],
            ['description' => 'Smoke and fire spread from a plugged-in mosquito coil near a bamboo wall.', 'cause' => 'Unattended katol ignited combustible wall material'],
        ],
        'rubbish_fire' => [
            ['description' => 'Sinisiga ang tuyong dahon sa bakanteng lote at kumalat ang apoy dahil sa hangin.', 'cause' => 'Unattended open burning of dry leaves'],
            ['description' => 'Dry grass and roadside rubbish are burning close to several homes.', 'cause' => 'Discarded cigarette butt ignited dry vegetation'],
            ['description' => 'May makapal na usok mula sa tambak na basura sa likod ng covered court.', 'cause' => 'Open burning of mixed household waste'],
            ['description' => 'A small brush fire is spreading through cogon grass near a farm boundary.', 'cause' => 'Wind carried embers from agricultural burning'],
        ],
        'commercial_fire' => [
            ['description' => 'Heavy smoke is coming from the kitchen exhaust of a roadside eatery.', 'cause' => 'Grease buildup in the kitchen exhaust duct'],
            ['description' => 'Nagliyab ang electrical panel sa loob ng sari-sari store malapit sa palengke.', 'cause' => 'Loose electrical connection in the main panel'],
            ['description' => 'Cardboard stocks behind a bakery oven caught fire and spread to shelving.', 'cause' => 'Combustible packaging stored too close to an oven'],
            ['description' => 'A repair shop caught fire while fuel was being drained from a motorcycle.', 'cause' => 'Gasoline vapor ignited by an electrical spark'],
        ],
        'vehicular_fire' => [
            ['description' => 'Nasusunog ang engine bay ng van sa gilid ng highway; nakalabas na ang mga sakay.', 'cause' => 'Vehicle electrical short circuit in the battery cable'],
            ['description' => 'A delivery truck rear tire and brake assembly caught fire while travelling.', 'cause' => 'Overheated binding brake ignited a rear tire'],
            ['description' => 'Smoke and flames are coming from beneath a parked passenger jeepney.', 'cause' => 'Leaking fuel line reached the hot exhaust manifold'],
            ['description' => 'A motorcycle caught fire after sparks appeared below the seat.', 'cause' => 'Improvised accessory wiring short circuit'],
        ],
        'storage_fire' => [
            ['description' => 'A bodega containing cartons and wooden pallets has visible flames at the rear.', 'cause' => 'Faulty wiring above stacked combustible inventory'],
            ['description' => 'Umuusok at nagliliyab ang imbakan ng feeds at tuyong copra.', 'cause' => 'Spontaneous heating in damp stored agricultural products'],
            ['description' => 'Paint and solvent containers are burning inside a small storage shed.', 'cause' => 'Flammable vapor ignited by a light-switch spark'],
            ['description' => 'A lumber stockpile caught fire near the warehouse loading area.', 'cause' => 'Welding sparks reached dry timber offcuts'],
        ],
        'others' => [
            ['description' => 'A downed electrical line is arcing against tree branches and a metal fence.', 'cause' => 'Fallen branch damaged an energized service line'],
            ['description' => 'Nagliyab ang transformer sa poste at may tumutulong mainit na langis.', 'cause' => 'Distribution transformer electrical failure'],
            ['description' => 'Fire is visible in a small farm equipment shed away from nearby houses.', 'cause' => 'Overheated portable generator beside fuel containers'],
            ['description' => 'A fireworks remnant ignited a tarpaulin and nearby dry decorations.', 'cause' => 'Fireworks ember landed on combustible decorations'],
        ],
    ];

    /** @var list<string> */
    private const INVALID_DESCRIPTIONS = [
        'Akala namin sunog, pero usok lang pala mula sa iniihaw na pagkain sa bakuran.',
        'Reported orange glow was confirmed as a controlled welding operation.',
        'Makapal na singaw mula sa dryer ang napagkamalang usok ng sunog.',
        'A distant bonfire was already contained inside a designated pit.',
        'Reported smoke was dust illuminated by vehicle headlights.',
        'Napagkamalang apoy ang maliwanag na ilaw at reflection sa bubong.',
        'Steam from a commercial kitchen vent was mistaken for smoke.',
        'The reported alarm came from a detector test; no smoke or heat was found.',
    ];

    public function run(): void
    {
        $residents = DB::table('users')->where('role', 'resident')->where('status', 'active')->orderBy('user_id')->get();
        $responders = DB::table('users')
            ->whereIn('role', ['bfp_personnel', 'bfp_admin'])
            ->where('status', 'active')
            ->orderBy('user_id')
            ->pluck('user_id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
        $barangays = DB::table('barangay')->orderBy('barangay_id')->get();

        if ($residents->isEmpty() || $responders === [] || $barangays->isEmpty()) {
            throw new RuntimeException('RealisticIncidentDataSeeder requires existing residents, BFP users, and barangays.');
        }

        if (DB::table('afor_report')->count() > 0) {
            throw new RuntimeException('Cannot rebuild incident data while out-of-scope afor_report rows reference incident_record.');
        }

        $residentBarangays = DB::table('resident_address')
            ->whereNotNull('user_id')
            ->whereNotNull('barangay_id')
            ->pluck('barangay_id', 'user_id');
        $reportImagePaths = DB::table('community_report')
            ->whereNotNull('report_image')
            ->distinct()
            ->pluck('report_image')
            ->filter()
            ->values()
            ->all();
        $evidenceImagePaths = DB::table('report_evidence')
            ->pluck('image_path')
            ->merge($reportImagePaths)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $now = Date::now();

        DB::transaction(function () use ($barangays, $evidenceImagePaths, $now, $reportImagePaths, $residentBarangays, $residents, $responders): void {
            $this->clearScopedTables();
            $this->seedReports($residents, $responders, $barangays, $residentBarangays, $reportImagePaths, $evidenceImagePaths, $now);
        });
    }

    private function clearScopedTables(): void
    {
        DB::table('report_link')->delete();
        DB::table('report_evidence')->delete();
        DB::table('report_status_history')->delete();
        DB::table('incident_record')->delete();
        DB::table('community_report')->delete();
    }

    /**
     * @param  Collection<int, object>  $residents
     * @param  list<int>  $responders
     * @param  Collection<int, object>  $barangays
     * @param  Collection<int|string, mixed>  $residentBarangays
     * @param  list<string>  $reportImagePaths
     * @param  list<string>  $evidenceImagePaths
     */
    private function seedReports(
        Collection $residents,
        array $responders,
        Collection $barangays,
        Collection $residentBarangays,
        array $reportImagePaths,
        array $evidenceImagePaths,
        CarbonInterface $now,
    ): void {
        $historicalTotal = array_sum(array_slice(self::MONTHLY_VOLUMES, 0, -1));
        $barangayPool = $this->weightedBarangayPool($barangays);
        $globalIndex = 0;
        $incidentIndex = 0;
        $resolvedSinceDuplicate = 0;
        $activeIndex = 0;
        $lastResolvedContext = null;

        foreach (self::MONTHLY_VOLUMES as $monthIndex => $volume) {
            $monthsAgo = count(self::MONTHLY_VOLUMES) - 1 - $monthIndex;
            $monthStart = $now->copy()->subMonthsNoOverflow($monthsAgo)->startOfMonth();

            for ($monthlyIndex = 0; $monthlyIndex < $volume; $monthlyIndex++, $globalIndex++) {
                $status = $this->statusFor($globalIndex, $historicalTotal, $monthsAgo, $monthlyIndex);
                $isActive = in_array($status, ['pending', 'accepted', 'dispatched'], true);
                $createdAt = $this->createdAt($now, $monthStart, $monthsAgo, $monthlyIndex, $isActive ? $activeIndex++ : null);
                $reporter = $this->reporterFor($residents, $residentBarangays, $globalIndex);
                $barangay = $barangayPool[($globalIndex * 17 + $monthIndex * 11) % count($barangayPool)];

                if ($reporter !== null && $residentBarangays->has($reporter->user_id)) {
                    $residentBarangay = $barangays->firstWhere('barangay_id', (int) $residentBarangays->get($reporter->user_id));
                    $barangay = $residentBarangay ?? $barangay;
                }

                $hasIncident = $status === 'resolved';
                $incidentType = $hasIncident ? $this->incidentTypeFor($incidentIndex) : $this->incidentTypeFor($globalIndex);
                $scenario = $this->scenarioFor($incidentType, $globalIndex);
                $alarmLevel = $hasIncident ? $this->alarmLevelFor($incidentIndex) : '1st_alarm';
                $coordinates = $this->pointWithinBarangay($barangay, $globalIndex + 1);
                $isDuplicate = false;

                if (
                    $status === 'resolved'
                    && $resolvedSinceDuplicate >= 44
                    && is_array($lastResolvedContext)
                    && $lastResolvedContext['created_at']->isSameMonth($createdAt)
                ) {
                    $isDuplicate = true;
                    $createdAt = $lastResolvedContext['created_at']->copy()->addMinutes(2 + ($globalIndex % 4));
                    $barangay = $lastResolvedContext['barangay'];
                    $coordinates = $lastResolvedContext['coordinates'];
                    $incidentType = $lastResolvedContext['incident_type'];
                    $alarmLevel = $lastResolvedContext['alarm_level'];
                    $scenario = $lastResolvedContext['scenario'];
                }

                $timeline = $this->timelineFor($status, $createdAt, $alarmLevel, $responders, $globalIndex);
                $updatedAt = $timeline[array_key_last($timeline)]['created_at'];
                [$deviceLatitude, $deviceLongitude, $accuracy] = $this->deviceCoordinates($coordinates[0], $coordinates[1], $globalIndex);
                $isInvalid = $status === 'invalid';
                $description = $isInvalid ? self::INVALID_DESCRIPTIONS[$globalIndex % count(self::INVALID_DESCRIPTIONS)] : $scenario['description'];
                $reportingResidentId = $reporter?->user_id ?? $residents[$globalIndex % $residents->count()]->user_id;

                $reportId = (int) DB::table('community_report')->insertGetId([
                    'user_id' => $reportingResidentId,
                    'reporter_name' => $reporter !== null ? trim($reporter->first_name.' '.$reporter->last_name) : self::WALK_IN_NAMES[$globalIndex % count(self::WALK_IN_NAMES)],
                    'contact_number' => $reporter?->contact_number ?? sprintf('09%09d', 100000000 + (($globalIndex * 7919) % 899999999)),
                    'description' => $description,
                    'report_image' => $reportImagePaths !== [] && $globalIndex % 3 === 0 ? $reportImagePaths[$globalIndex % count($reportImagePaths)] : null,
                    'ai_fire_label' => $isInvalid ? ($globalIndex % 2 === 0 ? 'no_fire' : 'uncertain') : ($incidentType === 'rubbish_fire' ? 'smoke_fire' : 'fire'),
                    'ai_fire_confidence' => $isInvalid ? round(0.12 + $this->fraction($globalIndex, 1) * 0.36, 4) : round(0.55 + $this->fraction($globalIndex, 2) * 0.44, 4),
                    'ai_verified_at' => $createdAt->copy()->addMinutes(1 + ($globalIndex % 4)),
                    'latitude' => $coordinates[0],
                    'longitude' => $coordinates[1],
                    'barangay_id' => $barangay->barangay_id,
                    'location_accuracy_m' => $accuracy,
                    'device_latitude' => $deviceLatitude,
                    'device_longitude' => $deviceLongitude,
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                DB::table('report_status_history')->insert(array_map(
                    fn (array $history): array => ['report_id' => $reportId, ...$history],
                    $timeline,
                ));

                if ($hasIncident) {
                    DB::table('incident_record')->insert([
                        'report_id' => $reportId,
                        'barangay_id' => $barangay->barangay_id,
                        'incident_datetime' => $createdAt->copy()->subMinutes($incidentIndex % 8),
                        'incident_type' => $incidentType,
                        'alarm_level' => $alarmLevel,
                        'cause_of_fire' => $scenario['cause'],
                        'casualties' => $this->casualtiesFor($alarmLevel, $incidentIndex),
                        'notes' => $status === 'resolved'
                            ? 'Scene secured after suppression and overhaul. Initial investigation documented.'
                            : 'Response operations are ongoing; incident details are subject to final assessment.',
                    ]);
                    $incidentIndex++;
                }

                if ($evidenceImagePaths !== [] && $globalIndex % 19 === 0) {
                    DB::table('report_evidence')->insert([
                        'report_id' => $reportId,
                        'image_path' => $evidenceImagePaths[$globalIndex % count($evidenceImagePaths)],
                        'uploaded_by' => $responders[$globalIndex % count($responders)],
                        'caption' => $isInvalid ? 'Verification photo from the reported location' : 'Scene photo received during response',
                        'created_at' => $createdAt->copy()->addMinutes(3),
                    ]);
                }

                if ($status === 'resolved') {
                    if ($isDuplicate) {
                        DB::table('report_link')->insert([
                            'main_report_id' => $lastResolvedContext['report_id'],
                            'related_report_id' => $reportId,
                        ]);
                        $resolvedSinceDuplicate = 0;
                    } else {
                        $resolvedSinceDuplicate++;
                    }

                    $lastResolvedContext = [
                        'report_id' => $reportId,
                        'created_at' => $createdAt,
                        'barangay' => $barangay,
                        'coordinates' => $coordinates,
                        'incident_type' => $incidentType,
                        'alarm_level' => $alarmLevel,
                        'scenario' => $scenario,
                    ];
                }
            }
        }
    }

    private function statusFor(int $globalIndex, int $historicalTotal, int $monthsAgo, int $monthlyIndex): string
    {
        if ($monthsAgo > 0) {
            return (($globalIndex * 37) % $historicalTotal) < 36 ? 'invalid' : 'resolved';
        }

        return match (true) {
            $monthlyIndex < 19 => 'resolved',
            $monthlyIndex < 35 => 'pending',
            $monthlyIndex < 51 => 'accepted',
            default => 'dispatched',
        };
    }

    private function createdAt(
        CarbonInterface $now,
        CarbonInterface $monthStart,
        int $monthsAgo,
        int $monthlyIndex,
        ?int $activeIndex,
    ): CarbonInterface {
        if ($activeIndex !== null) {
            $createdAt = $now->copy()
                ->subDays($activeIndex % 8)
                ->setTime(self::REPORTING_HOURS[($activeIndex * 7) % count(self::REPORTING_HOURS)], ($activeIndex * 13) % 60);

            return $createdAt->greaterThan($now->copy()->subMinutes(30))
                ? $now->copy()->subMinutes(45 + ($activeIndex % 60))
                : $createdAt;
        }

        if ($monthsAgo === 0 && $monthlyIndex === 0) {
            return $now->copy()->subHours(8);
        }

        if ($monthsAgo === 0 && $monthlyIndex === 1) {
            return $now->copy()->subDay()->setTime(14, 20);
        }

        $maximumDay = $monthsAgo === 1
            ? min($monthStart->daysInMonth, $now->day)
            : ($monthsAgo === 0 ? max(1, $now->day - 9) : $monthStart->daysInMonth);
        $day = 1 + (($monthlyIndex * 7 + $monthsAgo * 3) % $maximumDay);

        return $monthStart->copy()->setDay($day)->setTime(
            self::REPORTING_HOURS[($monthlyIndex * 3 + $monthsAgo) % count(self::REPORTING_HOURS)],
            ($monthlyIndex * 17 + $monthsAgo * 11) % 60,
        );
    }

    /**
     * @param  Collection<int, object>  $residents
     * @param  Collection<int|string, mixed>  $residentBarangays
     */
    private function reporterFor(Collection $residents, Collection $residentBarangays, int $index): ?object
    {
        if ($index % 7 === 0) {
            return null;
        }

        if ($index % 10 === 0 && $residentBarangays->isNotEmpty()) {
            $addressedUserId = (int) $residentBarangays->keys()->values()[$index % $residentBarangays->count()];

            return $residents->firstWhere('user_id', $addressedUserId) ?? $residents[($index * 11) % $residents->count()];
        }

        return $residents[($index * 11) % $residents->count()];
    }

    /**
     * @param  Collection<int, object>  $barangays
     * @return list<object>
     */
    private function weightedBarangayPool(Collection $barangays): array
    {
        $hotspotWeights = [
            'Barangay 1 (Poblacion)' => 10,
            'Binubusan' => 9,
            'Lumaniag' => 8,
            'Matabungkay' => 7,
            'Prenza' => 6,
        ];
        $pool = [];

        foreach ($barangays->values() as $index => $barangay) {
            $weight = $hotspotWeights[$barangay->barangay_name] ?? 1 + ($index % 3);

            for ($repeat = 0; $repeat < $weight; $repeat++) {
                $pool[] = $barangay;
            }
        }

        return $pool;
    }

    /** @return array{description: string, cause: string} */
    private function scenarioFor(string $incidentType, int $index): array
    {
        $scenarios = self::SCENARIOS[$incidentType];

        return $scenarios[$index % count($scenarios)];
    }

    private function incidentTypeFor(int $index): string
    {
        $position = ($index * 37) % 100;

        return match (true) {
            $position < 40 => 'residential_fire',
            $position < 60 => 'rubbish_fire',
            $position < 75 => 'commercial_fire',
            $position < 85 => 'vehicular_fire',
            $position < 93 => 'storage_fire',
            default => 'others',
        };
    }

    private function alarmLevelFor(int $index): string
    {
        if ($index === 127) {
            return 'task_force_alpha';
        }

        if ($index === 251) {
            return 'general_alarm';
        }

        if ($index === 309) {
            return 'task_force_bravo';
        }

        $position = ($index * 29) % 100;

        return match (true) {
            $position < 68 => '1st_alarm',
            $position < 89 => '2nd_alarm',
            $position < 95 => '3rd_alarm',
            $position < 97 => '4th_alarm',
            default => '5th_alarm',
        };
    }

    private function casualtiesFor(string $alarmLevel, int $index): int
    {
        if (in_array($alarmLevel, ['task_force_alpha', 'task_force_bravo', 'general_alarm'], true)) {
            return 1 + ($index % 3);
        }

        if (in_array($alarmLevel, ['4th_alarm', '5th_alarm'], true) && $index % 4 === 0) {
            return 1 + ($index % 2);
        }

        return $index % 71 === 0 ? 1 : 0;
    }

    /**
     * @param  list<int>  $responders
     * @return list<array{status: string, notes: string, changed_by: int, created_at: CarbonInterface}>
     */
    private function timelineFor(
        string $status,
        CarbonInterface $createdAt,
        string $alarmLevel,
        array $responders,
        int $index,
    ): array {
        $timeline = [[
            'status' => 'pending',
            'notes' => 'Report received; location and caller details logged by the duty desk.',
            'changed_by' => $responders[$index % count($responders)],
            'created_at' => $createdAt,
        ]];

        if ($status === 'pending') {
            return $timeline;
        }

        if ($status === 'invalid') {
            $timeline[] = [
                'status' => 'invalid',
                'notes' => 'No active fire found after verification; report closed as invalid.',
                'changed_by' => $responders[($index + 1) % count($responders)],
                'created_at' => $createdAt->copy()->addMinutes(5 + ($index % 21)),
            ];

            return $timeline;
        }

        $acceptedAt = $createdAt->copy()->addMinutes(2 + ($index % 9));
        $timeline[] = [
            'status' => 'accepted',
            'notes' => 'Visible fire indicators confirmed; incident accepted for response.',
            'changed_by' => $responders[($index + 1) % count($responders)],
            'created_at' => $acceptedAt,
        ];

        if ($status === 'accepted') {
            return $timeline;
        }

        $dispatchedAt = $acceptedAt->copy()->addMinutes(1 + ($index % 5));
        $timeline[] = [
            'status' => 'dispatched',
            'notes' => $index % 2 === 0 ? 'Engine 1 en route with the duty crew.' : 'Engine unit and responders dispatched to the scene.',
            'changed_by' => $responders[($index + 2) % count($responders)],
            'created_at' => $dispatchedAt,
        ];

        if ($status === 'dispatched') {
            return $timeline;
        }

        $timeline[] = [
            'status' => 'resolved',
            'notes' => $index % 2 === 0 ? 'Fire under control; overhaul completed and scene declared safe.' : 'Fire out, declared by the ground fire officer.',
            'changed_by' => $responders[($index + 3) % count($responders)],
            'created_at' => $createdAt->copy()->addMinutes($this->resolutionMinutes($alarmLevel, $index)),
        ];

        return $timeline;
    }

    private function resolutionMinutes(string $alarmLevel, int $index): int
    {
        [$minimum, $maximum] = match ($alarmLevel) {
            '1st_alarm' => [20, 80],
            '2nd_alarm' => [45, 150],
            '3rd_alarm' => [90, 240],
            '4th_alarm' => [150, 300],
            '5th_alarm' => [180, 340],
            default => [240, 360],
        };

        return $minimum + ($index * 17) % ($maximum - $minimum + 1);
    }

    /** @return array{0: float, 1: float} */
    private function pointWithinBarangay(object $barangay, int $seed): array
    {
        $boundary = json_decode((string) $barangay->boundary_geojson, true);
        $ring = $this->boundaryRing($boundary);

        if ($ring === []) {
            return [(float) $barangay->centroid_lat, (float) $barangay->centroid_lng];
        }

        $longitudes = array_column($ring, 0);
        $latitudes = array_column($ring, 1);
        $minimumLongitude = min($longitudes);
        $maximumLongitude = max($longitudes);
        $minimumLatitude = min($latitudes);
        $maximumLatitude = max($latitudes);

        for ($attempt = 0; $attempt < 500; $attempt++) {
            $latitude = $minimumLatitude + ($maximumLatitude - $minimumLatitude) * $this->fraction($seed, $attempt * 2 + 7);
            $longitude = $minimumLongitude + ($maximumLongitude - $minimumLongitude) * $this->fraction($seed, $attempt * 2 + 8);

            if (Geo::pointInPolygon($latitude, $longitude, $ring)) {
                return [round($latitude, 8), round($longitude, 8)];
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
        $distanceMeters = 5 + ($seed % 26);
        $bearing = deg2rad(($seed * 47) % 360);
        $latitudeOffset = cos($bearing) * $distanceMeters / 111320;
        $longitudeScale = max(0.1, cos(deg2rad($latitude)));
        $longitudeOffset = sin($bearing) * $distanceMeters / (111320 * $longitudeScale);

        return [
            round($latitude + $latitudeOffset, 8),
            round($longitude + $longitudeOffset, 8),
            5 + ($seed * 7) % 46,
        ];
    }

    private function fraction(int $seed, int $salt): float
    {
        return hexdec(substr(hash('sha256', $seed.'-'.$salt), 0, 8)) / 4294967295;
    }
}
