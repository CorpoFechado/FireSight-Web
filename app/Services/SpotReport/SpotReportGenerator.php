<?php

namespace App\Services\SpotReport;

use App\Support\SpotReport\SpotReportData;

interface SpotReportGenerator
{
    /**
     * Render the Spot Investigation Report and return the path of the generated file.
     */
    public function generate(SpotReportData $data): string;
}
