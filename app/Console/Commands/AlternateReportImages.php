<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AlternateReportImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firesight:alternate-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alternate all community report images among 3 selected photos and delete remaining report photos.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting community report image alternation and cleanup...');

        $allowedImages = [
            'uploads/reports/report_44_1793446882_aca83633.jpg',
            'uploads/reports/report_44_1794347202_72f2e635.jpg',
            'uploads/reports/report_44_1793496551_6580dd19.jpg',
        ];

        $allowedFilenames = [
            'report_44_1793446882_aca83633.jpg',
            'report_44_1794347202_72f2e635.jpg',
            'report_44_1793496551_6580dd19.jpg',
        ];

        // 1. Update database records in community_report
        $reports = DB::table('community_report')->orderBy('report_id')->get(['report_id']);
        $totalReports = $reports->count();
        $this->info("Found {$totalReports} community reports in firesight_db2.");

        $numImages = count($allowedImages);
        $updatedCount = 0;

        foreach ($reports as $index => $report) {
            $assignedImage = $allowedImages[$index % $numImages];
            DB::table('community_report')
                ->where('report_id', $report->report_id)
                ->update([
                    'report_image' => $assignedImage,
                    'updated_at' => now(),
                ]);
            $updatedCount++;
        }

        $this->info("Successfully updated {$updatedCount} community reports to alternate across the 3 images.");

        // 2. Clean up storage/app/public/uploads/reports
        $reportsDir = storage_path('app/public/uploads/reports');
        $deletedFiles = 0;

        if (File::isDirectory($reportsDir)) {
            $files = File::files($reportsDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                if (! in_array($filename, $allowedFilenames, true)) {
                    File::delete($file->getPathname());
                    $deletedFiles++;
                    $this->line("Deleted: {$filename}");
                } else {
                    $this->line("Kept: {$filename}");
                }
            }
        }

        // Also check if public/uploads/reports exists independently (not as symlink)
        $publicUploads = public_path('uploads/reports');
        if (File::isDirectory($publicUploads) && ! is_link($publicUploads)) {
            $files = File::files($publicUploads);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                if (! in_array($filename, $allowedFilenames, true)) {
                    File::delete($file->getPathname());
                    $deletedFiles++;
                }
            }
        }

        $this->info("Deleted {$deletedFiles} unneeded community report photos from backend.");
        $this->info('Alternation and cleanup complete.');

        return Command::SUCCESS;
    }
}
