<?php

namespace App\Console\Commands;

use Base64Url\Base64Url;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid
        {--show : Display the generated keys instead of modifying .env}
        {--force : Overwrite existing VAPID keys in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        $keys = $this->generateVapidKeys();

        if ($this->option('show') || ! File::exists($this->laravel->environmentFilePath())) {
            $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
            $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);

            return self::SUCCESS;
        }

        $envPath = $this->laravel->environmentFilePath();
        $contents = File::get($envPath);

        $hasPublic = preg_match('/^VAPID_PUBLIC_KEY=.+$/m', $contents) === 1;
        $hasPrivate = preg_match('/^VAPID_PRIVATE_KEY=.+$/m', $contents) === 1;

        if (($hasPublic || $hasPrivate) && ! $this->option('force')) {
            $this->components->warn('VAPID keys already exist in .env. Use --force to overwrite them or --show to print new keys.');

            return self::SUCCESS;
        }

        $contents = $this->upsertEnvKey($contents, 'VAPID_PUBLIC_KEY', $keys['publicKey']);
        $contents = $this->upsertEnvKey($contents, 'VAPID_PRIVATE_KEY', $keys['privateKey']);

        if (preg_match('/^VAPID_SUBJECT=/m', $contents) !== 1) {
            $contents = $this->upsertEnvKey($contents, 'VAPID_SUBJECT', 'mailto:admin@firesight.local');
        }

        File::put($envPath, $contents);

        $this->components->info('VAPID keys generated and saved to .env.');

        return self::SUCCESS;
    }

    private function upsertEnvKey(string $contents, string $key, string $value): string
    {
        $line = "{$key}={$value}";

        if (preg_match("/^{$key}=.*$/m", $contents) === 1) {
            return (string) preg_replace("/^{$key}=.*$/m", $line, $contents, 1);
        }

        $separator = str_ends_with($contents, "\n") || $contents === '' ? '' : PHP_EOL;

        return $contents.$separator.$line.PHP_EOL;
    }

    /**
     * @return array{publicKey: string, privateKey: string}
     */
    private function generateVapidKeys(): array
    {
        try {
            $keys = VAPID::createVapidKeys();

            return [
                'publicKey' => (string) ($keys['publicKey'] ?? ''),
                'privateKey' => (string) ($keys['privateKey'] ?? ''),
            ];
        } catch (\Throwable $e) {
            $fallbackConfig = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';

            if (! File::exists($fallbackConfig)) {
                throw $e;
            }

            $resource = openssl_pkey_new([
                'curve_name' => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'config' => $fallbackConfig,
            ]);

            if ($resource === false) {
                throw $e;
            }

            $details = openssl_pkey_get_details($resource);
            if (! is_array($details) || ! isset($details['ec']['x'], $details['ec']['y'], $details['ec']['d'])) {
                throw $e;
            }

            $x = str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT);
            $y = str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);
            $d = str_pad($details['ec']['d'], 32, "\x00", STR_PAD_LEFT);

            return [
                'publicKey' => Base64Url::encode("\x04".$x.$y),
                'privateKey' => Base64Url::encode($d),
            ];
        }
    }
}
