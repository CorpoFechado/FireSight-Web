<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PredictFireRisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firesight:predict-risk
                            {--as-of= : Cutoff month for historical data in YYYY-MM format, e.g. 2025-11}
                            {--dry-run : Generate predictions and CSV without saving to MySQL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the XGBoost monthly fire risk prediction model using live database records.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting FireSight XGBoost Monthly Fire Risk Prediction...');

        $scriptDir = base_path('risk-prediction-service');
        $scriptPath = $scriptDir.DIRECTORY_SEPARATOR.'predict_risk.py';

        if (! file_exists($scriptPath)) {
            $this->error("Prediction script not found at: {$scriptPath}");

            return self::FAILURE;
        }

        $pythonBin = $this->resolvePythonBinary($scriptDir);
        $this->comment("Using Python: {$pythonBin}");

        $command = [$pythonBin, $scriptPath];

        if ($asOf = $this->option('as-of')) {
            $command[] = '--as-of';
            $command[] = (string) $asOf;
        }

        if (! $this->option('dry-run')) {
            $command[] = '--write-db';
        }

        $process = new Process($command, $scriptDir, [
            'DB_HOST' => config('database.connections.mysql.host', '127.0.0.1'),
            'DB_PORT' => (string) config('database.connections.mysql.port', 3306),
            'DB_DATABASE' => config('database.connections.mysql.database', 'firesight_db2'),
            'DB_USERNAME' => config('database.connections.mysql.username', 'root'),
            'DB_PASSWORD' => (string) config('database.connections.mysql.password', ''),
        ], null, 120);

        $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error('Fire risk prediction process encountered an error.');

            return self::FAILURE;
        }

        $this->info('Fire risk prediction completed successfully.');

        return self::SUCCESS;
    }

    /**
     * Locates the active Python executable (.venv or system python).
     */
    private function resolvePythonBinary(string $serviceDir): string
    {
        $windowsVenv = $serviceDir.DIRECTORY_SEPARATOR.'.venv'.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe';
        if (file_exists($windowsVenv)) {
            return $windowsVenv;
        }

        $unixVenv = $serviceDir.DIRECTORY_SEPARATOR.'.venv'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'python';
        if (file_exists($unixVenv)) {
            return $unixVenv;
        }

        return PHP_OS_FAMILY === 'Windows' ? 'python.exe' : 'python3';
    }
}
