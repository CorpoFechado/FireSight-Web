<?php

namespace App\Console\Commands;

use App\Support\JmeterTestAccounts;
use Illuminate\Console\Command;

class InspectJmeterTestAccounts extends Command
{
    protected $signature = 'jmeter:inspect-test-accounts';

    protected $description = 'Preview test-account ownership and references; never deletes data';

    public function handle(JmeterTestAccounts $accounts): int
    {
        $result = $accounts->cleanup(false);
        $this->info('Dry run only. Owned accounts found: '.$result['accounts']);
        $this->line('Owned sessions: '.$result['sessions']);
        foreach ($result['references'] as $reference => $count) {
            $this->warn($reference.': '.$count.' business reference(s); deletion is prohibited.');
        }

        return self::SUCCESS;
    }
}
