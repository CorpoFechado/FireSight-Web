<?php

namespace App\Console\Commands;

use App\Support\JmeterTestAccounts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VerifyJmeterEnvironment extends Command
{
    protected $signature = 'jmeter:verify-environment {--require-empty : Refuse a database containing tables or views}';

    protected $description = 'Read-only verification of the isolated JMeter database environment';

    public function handle(JmeterTestAccounts $accounts): int
    {
        if (! app()->environment('jmeter') || app()->environmentFile() !== '.env.jmeter' || app()->configurationIsCached()
            || app()->getCachedConfigPath() !== base_path('bootstrap/cache/jmeter-config.php')) {
            throw new RuntimeException('Refusing: expected uncached .env.jmeter and jmeter environment.');
        }
        $username = config('database.connections.mysql.username');
        if ($username !== 'firesight_jmeter' || ! is_string(config('database.connections.mysql.password'))
            || config('database.connections.mysql.password') === '') {
            throw new RuntimeException('Refusing: configure the dedicated database username and a nonempty password.');
        }
        if (config('session.driver') !== 'database' || ! in_array(config('session.connection'), [null, 'mysql'], true)
            || config('cache.default') !== 'database' || ! in_array(config('cache.stores.database.connection'), [null, 'mysql'], true)
            || ! in_array(config('cache.stores.database.lock_connection'), [null, 'mysql'], true)) {
            throw new RuntimeException('Refusing: session or cache connection does not use isolated MySQL.');
        }
        $accounts->verifyTarget();
        $identity = DB::selectOne('SELECT CURRENT_USER() AS effective_account');
        if (explode('@', $identity->effective_account, 2)[0] !== 'firesight_jmeter') {
            throw new RuntimeException('Refusing: effective database account is not the dedicated test user.');
        }
        $tables = DB::selectOne('SELECT COUNT(*) AS table_count FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        if ($this->option('require-empty') && (int) $tables->table_count !== 0) {
            throw new RuntimeException('Refusing initial migration: target is not empty.');
        }
        $this->info('Environment: jmeter; environment file: .env.jmeter; configuration cache: disabled.');
        $this->info('Connected database: firesight_jmeter_test; configured host: 127.0.0.1; server port: 3306.');
        $this->line('Tables visible to this database user: '.(int) $tables->table_count);
        $this->line('Session and cache stores use the isolated MySQL connection.');

        return self::SUCCESS;
    }
}
