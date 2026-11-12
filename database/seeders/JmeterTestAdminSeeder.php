<?php

namespace Database\Seeders;

use App\Support\JmeterTestAccounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class JmeterTestAdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = app(JmeterTestAccounts::class);
        $accounts->verifyTarget();
        $accounts->verifyAccountSchema();
        $accounts->verifyWriteApproval();
        $count = filter_var(getenv('FIRESIGHT_JMETER_ACCOUNT_COUNT'), FILTER_VALIDATE_INT);
        if ($count === false || $count < 1 || ($count > 50 && $count !== 200)) {
            throw new RuntimeException('Specify FIRESIGHT_JMETER_ACCOUNT_COUNT from 1 to 50, or 200.');
        }
        $accounts->exclusively(function () use ($accounts, $count): void {
            $manifest = $accounts->manifest(true);
            if (! empty($manifest['cleaned_at'])) {
                throw new RuntimeException('This manifest has already been cleaned; retain it for audit and review a new run.');
            }
            if ($count === 200) {
                $manifest = $accounts->extendManifest($manifest);
            }
            $entries = $manifest['entries'];
            $manifest['baseline'] = DB::transaction(function () use ($accounts, $count, $entries): array {
                foreach ($entries as $entry) {
                    $accounts->ownedUser($entry);
                }
                $before = $accounts->businessFingerprint($entries);
                foreach (array_slice($entries, 0, $count) as $index => $entry) {
                    if ($accounts->ownedUser($entry) !== null) {
                        continue;
                    }
                    $userId = DB::table('users')->insertGetId([
                        'role' => 'bfp_admin', 'status' => 'active', 'first_name' => 'JMeter',
                        'last_name' => sprintf('TestAdmin%02d', $index + 1), 'email' => $entry['email'],
                        'email_verified_at' => now(), 'is_verified' => true, 'password' => Hash::make($entry['password']),
                        'created_at' => now(), 'updated_at' => now(),
                    ], 'user_id');
                    DB::table('bfp_personnel_details')->insert([
                        'user_id' => $userId, 'rank' => 'Test Administrator', 'station_assigned' => 'Test only',
                        'employee_number' => $entry['marker'], 'duty_group' => null,
                    ]);
                }
                if ($before !== $accounts->businessFingerprint($entries)) {
                    throw new RuntimeException('Unrelated data changed during provisioning; rolling back.');
                }
                $accounts->verifyWriteApproval();

                return $before;
            });

            $provisioned = [];
            foreach ($entries as $index => $entry) {
                $user = $accounts->ownedUser($entry);
                if ($user !== null) {
                    $entry['user_id'] = (int) $user->user_id;
                    $manifest['entries'][$index] = $entry;
                    $provisioned[] = $entry;
                }
            }
            $accounts->saveManifest($manifest);
            $accounts->writeCsv($provisioned);
        });
        $this->command?->info('Test administrator provisioning completed; private CSV updated.');
    }
}
