<?php

namespace App\Console\Commands;

use App\Support\JmeterTestAccounts;
use Illuminate\Console\Command;

class CleanupJmeterTestAccounts extends Command
{
    protected $signature = 'jmeter:cleanup-test-accounts {--dry-run : Preview only (the default)} {--execute : Delete reviewed manifest-owned accounts} {--confirm= : Required deletion acknowledgement}';

    protected $description = 'Preview or explicitly remove only manifest-owned JMeter accounts, personnel and sessions';

    public function handle(JmeterTestAccounts $accounts): int
    {
        $execute = (bool) $this->option('execute');
        if ($execute && ($this->option('dry-run') || $this->option('confirm') !== 'delete-manifest-owned-test-accounts')) {
            $this->error('Deletion requires --execute --confirm=delete-manifest-owned-test-accounts without --dry-run.');

            return self::FAILURE;
        }
        $result = $accounts->cleanup($execute);
        $this->info(($execute ? 'Cleanup completed' : 'Dry run only').': '.$result['accounts'].' owned account(s); '.$result['sessions'].' session(s).');
        foreach ($result['references'] as $reference => $count) {
            $this->warn($reference.': '.$count.' business reference(s); deletion is prohibited.');
        }

        return self::SUCCESS;
    }
}
