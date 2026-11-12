<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class JmeterTestAccounts
{
    private const DATABASE = 'firesight_db2';

    private const MANIFEST = 'manifest.json';

    private const CSV = 'accounts.csv';

    public function verifyTarget(): void
    {
        $config = config('database.connections.mysql');

        if (app()->configurationIsCached() || ! app()->environment('local') || app()->environmentFile() !== '.env' || config('database.default') !== 'mysql'
            || getenv('FIRESIGHT_JMETER_APPROVED_DB') !== self::DATABASE || ! is_array($config)
            || ($config['database'] ?? null) !== self::DATABASE || ($config['host'] ?? null) !== '127.0.0.1'
            || (string) ($config['port'] ?? '') !== '3306' || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Refusing: expected explicitly approved local firesight_db2 using the normal .env.');
        }

        $actual = DB::selectOne('SELECT DATABASE() AS db_name, @@port AS db_port');
        if ($actual->db_name !== self::DATABASE || (int) $actual->db_port !== 3306) {
            throw new RuntimeException('Refusing: connected development database identity does not match.');
        }
    }

    public function verifyWriteApproval(): void
    {
        if (getenv('FIRESIGHT_JMETER_RISK_ACK') !== 'reviewed-current-innodb-risk') {
            throw new RuntimeException('Explicit acknowledgement of the reviewed current InnoDB risk is required.');
        }
        $backup = getenv('FIRESIGHT_JMETER_BACKUP');
        $backupHash = getenv('FIRESIGHT_JMETER_BACKUP_SHA256');
        $logHash = getenv('FIRESIGHT_JMETER_APPROVED_LOG_SHA256');
        if (! is_string($backup) || ! is_file($backup) || ! is_readable($backup)
            || ! is_string($backupHash) || ! preg_match('/\A[a-f0-9]{64}\z/', $backupHash)
            || ! hash_equals($backupHash, hash_file('sha256', $backup))) {
            throw new RuntimeException('A readable backup matching its reviewed SHA-256 is required.');
        }
        $log = 'D:/xampp/mysql/data/mysql_error.log';
        clearstatcache(true, $log);
        if (! is_readable($log) || ! is_string($logHash) || ! preg_match('/\A[a-f0-9]{64}\z/', $logHash)
            || ! hash_equals($logHash, hash_file('sha256', $log))) {
            throw new RuntimeException('Database error log differs from the explicitly reviewed log; stop and investigate.');
        }
    }

    public function verifyAccountSchema(): void
    {
        foreach (['users', 'bfp_personnel_details', 'sessions'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException('Required account/session schema is missing.');
            }
        }
        $keys = Schema::getForeignKeys('bfp_personnel_details');
        if (! collect($keys)->contains(fn (array $key): bool => $key['columns'] === ['user_id'] && $key['foreign_table'] === 'users' && $key['foreign_columns'] === ['user_id'])) {
            throw new RuntimeException('Required personnel-to-user foreign key is missing.');
        }
        if (DB::getDriverName() === 'mysql') {
            $engines = DB::select('SELECT TABLE_NAME AS name, ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (?, ?, ?)', ['users', 'bfp_personnel_details', 'sessions']);
            foreach ($engines as $table) {
                if ($table->engine !== 'InnoDB') {
                    throw new RuntimeException('Transactional account/session tables are required.');
                }
            }
            $triggers = DB::selectOne('SELECT COUNT(*) AS total FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE IN (?, ?)', ['users', 'bfp_personnel_details']);
            if ((int) $triggers->total !== 0) {
                throw new RuntimeException('Unreviewed account-table triggers prevent provisioning.');
            }
        }
    }

    public function exclusively(\Closure $operation): mixed
    {
        $this->privateDirectory();
        $lock = fopen(base_path('jmeter/private/operation.lock'), 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new RuntimeException('Another test-account operation is active.');
        }
        try {
            return $operation();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function privateDirectory(): string
    {
        $directory = base_path('jmeter/private');
        if (! is_dir($directory) || is_link($directory) || getenv('FIRESIGHT_JMETER_PRIVATE_DIR_READY') !== '1') {
            throw new RuntimeException('Prepare and restrict jmeter/private, then explicitly acknowledge its permissions.');
        }

        return $directory;
    }

    /** @return array<string, mixed> */
    public function manifest(bool $create): array
    {
        $directory = $this->privateDirectory();

        $path = $directory.DIRECTORY_SEPARATOR.self::MANIFEST;
        if (! file_exists($path) && $create) {
            $entries = [];
            for ($i = 1; $i <= 50; $i++) {
                $entries[] = [
                    'email' => sprintf('testadmin%02d@example.test', $i),
                    'password' => bin2hex(random_bytes(24)),
                    'marker' => 'JM-'.bin2hex(random_bytes(16)),
                    'user_id' => null,
                ];
            }
            $handle = @fopen($path, 'x');
            if ($handle === false) {
                throw new RuntimeException('Cannot exclusively create private manifest.');
            }
            try {
                @chmod($path, 0600);
                $json = json_encode(['version' => 2, 'database' => self::DATABASE, 'entries' => $entries], JSON_THROW_ON_ERROR);
                if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                    throw new RuntimeException('Could not persist private manifest; preserve it for review.');
                }
            } finally {
                fclose($handle);
            }
        }

        if (is_link($path) || ! is_file($path)) {
            throw new RuntimeException('Private manifest is missing or is a symbolic link.');
        }
        $manifest = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($manifest) || ($manifest['version'] ?? null) !== 2 || ($manifest['database'] ?? null) !== self::DATABASE || ! is_array($manifest['entries'] ?? null) || ! in_array(count($manifest['entries']), [50, 200], true)) {
            throw new RuntimeException('Invalid private ownership manifest.');
        }
        foreach ($manifest['entries'] as $index => $entry) {
            if (! is_array($entry) || ($entry['email'] ?? null) !== sprintf('testadmin%02d@example.test', $index + 1)
                || ! preg_match('/\A[a-f0-9]{48}\z/', $entry['password'] ?? '')
                || ! preg_match('/\AJM-[a-f0-9]{32}\z/', $entry['marker'] ?? '')
                || ! array_key_exists('user_id', $entry) || ($entry['user_id'] !== null && (! is_int($entry['user_id']) || $entry['user_id'] < 1))) {
                throw new RuntimeException('Invalid private ownership manifest entry.');
            }
        }
        if (count(array_unique(array_column($manifest['entries'], 'marker'))) !== count($manifest['entries'])
            || count(array_unique(array_column($manifest['entries'], 'password'))) !== count($manifest['entries'])) {
            throw new RuntimeException('Private ownership manifest contains duplicate markers or passwords.');
        }

        return $manifest;
    }

    /** @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    public function extendManifest(array $manifest): array
    {
        if (count($manifest['entries']) === 200) {
            return $manifest;
        }
        foreach ($manifest['entries'] as $entry) {
            if ($entry['user_id'] === null || $this->ownedUser($entry) === null) {
                throw new RuntimeException('All original accounts must be present and owned before extending the manifest.');
            }
        }
        for ($i = 51; $i <= 200; $i++) {
            $manifest['entries'][] = [
                'email' => sprintf('testadmin%02d@example.test', $i),
                'password' => bin2hex(random_bytes(24)),
                'marker' => 'JM-'.bin2hex(random_bytes(16)),
                'user_id' => null,
            ];
        }
        $this->saveManifest($manifest);

        return $this->manifest(false);
    }

    /** @param array{email: string, password: string, marker: string, user_id: int|null} $entry */
    public function ownedUser(array $entry): ?object
    {
        $user = DB::table('users')->where('email', $entry['email'])->first();
        $details = DB::table('bfp_personnel_details')->where('employee_number', $entry['marker'])->first();
        if ($user === null && $details === null) {
            if ($entry['user_id'] !== null) {
                throw new RuntimeException('A previously recorded account is missing; manual ownership review is required.');
            }

            return null;
        }
        if ($user === null || $details === null || (int) $details->user_id !== (int) $user->user_id
            || ($entry['user_id'] !== null && $entry['user_id'] !== (int) $user->user_id)
            || $user->role !== 'bfp_admin' || $user->status !== 'active' || ! $user->email_verified_at || ! $user->is_verified
            || $user->two_factor_secret !== null || $user->two_factor_recovery_codes !== null || $user->two_factor_confirmed_at !== null
            || ! Hash::check($entry['password'], $user->password)) {
            throw new RuntimeException('Account collision or ownership mismatch; no account will be overwritten.');
        }

        return $user;
    }

    /** @param array<string, mixed> $manifest */
    public function saveManifest(array $manifest): void
    {
        $this->privateDirectory();
        $path = base_path('jmeter/private/'.self::MANIFEST);
        if (is_link($path)) {
            throw new RuntimeException('Refusing symbolic-link manifest target.');
        }
        $temporary = $path.'.'.bin2hex(random_bytes(8)).'.tmp';
        $json = json_encode($manifest, JSON_THROW_ON_ERROR);
        $file = fopen($temporary, 'x');
        if ($file === false) {
            throw new RuntimeException('Cannot create private manifest checkpoint.');
        }
        try {
            @chmod($temporary, 0600);
            if (fwrite($file, $json) !== strlen($json) || ! fflush($file)) {
                throw new RuntimeException('Cannot persist private manifest checkpoint.');
            }
        } finally {
            fclose($file);
        }
        if (! rename($temporary, $path)) {
            throw new RuntimeException('Cannot replace manifest; preserve the files and retry provisioning to reconcile IDs.');
        }
    }

    /** @param list<int> $ids
     * @return array<string, int>
     */
    public function businessReferences(array $ids): array
    {
        $counts = [];
        if ($ids === []) {
            return $counts;
        }
        foreach ($this->databaseTables() as $table) {
            $name = $table['name'];
            $columns = [];
            foreach (Schema::getForeignKeys($name) as $key) {
                if ($key['foreign_table'] === 'users' && $key['foreign_columns'] === ['user_id']) {
                    $columns = array_merge($columns, $key['columns']);
                }
            }
            foreach (Schema::getColumnListing($name) as $column) {
                if (in_array($column, ['user_id', 'created_by', 'updated_by', 'uploaded_by', 'changed_by', 'dispatched_by', 'verified_by', 'accepted_by', 'resolved_by'], true)) {
                    $columns[] = $column;
                }
            }
            foreach (array_unique($columns) as $column) {
                if (in_array($name, ['users', 'sessions', 'bfp_personnel_details'], true)) {
                    continue;
                }
                $count = DB::table($name)->whereIn($column, $ids)->count();
                if ($count > 0) {
                    $counts[$name.'.'.$column] = $count;
                }
            }
        }

        return $counts;
    }

    /** @param list<array{email: string, password: string, marker: string, user_id: int|null}> $entries
     * @return array<string, array{rows: int, sha256: string}>
     */
    public function businessFingerprint(array $entries): array
    {
        $result = [];
        $transient = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'auth_token', 'password_reset_tokens'];
        foreach ($this->databaseTables() as $table) {
            $name = $table['name'];
            if (in_array($name, $transient, true)) {
                continue;
            }
            $query = DB::table($name);
            if ($name === 'users') {
                $query->whereNotIn('email', array_column($entries, 'email'));
            } elseif ($name === 'bfp_personnel_details') {
                $query->whereNotIn('employee_number', array_column($entries, 'marker'));
            }
            foreach (Schema::getColumnListing($name) as $column) {
                $query->orderBy($column);
            }
            $hash = hash_init('sha256');
            $count = 0;
            foreach ($query->cursor() as $row) {
                hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR)."\n");
                $count++;
            }
            $result[$name] = ['rows' => $count, 'sha256' => hash_final($hash)];
        }
        ksort($result);

        return $result;
    }

    /** @return array<int, array<string, mixed>> */
    private function databaseTables(): array
    {
        return Schema::getTables(DB::getDriverName() === 'sqlite' ? null : DB::connection()->getDatabaseName());
    }

    /** @return array{accounts: int, sessions: int, references: array<string, int>} */
    public function cleanup(bool $execute): array
    {
        $this->verifyTarget();
        $this->verifyAccountSchema();
        if ($execute) {
            $this->verifyWriteApproval();
        }

        return $this->exclusively(function () use ($execute): array {
            $manifest = $this->manifest(false);
            if (! empty($manifest['cleaned_at'])) {
                return ['accounts' => 0, 'sessions' => 0, 'references' => []];
            }
            $result = DB::transaction(function () use ($manifest, $execute): array {
                $ids = [];
                DB::table('users')->whereIn('user_id', array_filter(array_column($manifest['entries'], 'user_id')))->lockForUpdate()->get();
                foreach ($manifest['entries'] as $entry) {
                    if ($entry['user_id'] !== null && ! DB::table('users')->where('user_id', $entry['user_id'])->exists()
                        && ! DB::table('users')->where('email', $entry['email'])->exists()
                        && ! DB::table('bfp_personnel_details')->where('employee_number', $entry['marker'])->exists()) {
                        continue;
                    }
                    $user = $this->ownedUser($entry);
                    if ($user !== null) {
                        if ($entry['user_id'] === null) {
                            throw new RuntimeException('Recorded IDs must be reconciled by provisioning before cleanup.');
                        }
                        $ids[] = $entry['user_id'];
                    }
                }
                $references = $this->businessReferences($ids);
                $result = ['accounts' => count($ids), 'sessions' => DB::table('sessions')->whereIn('user_id', $ids)->count(), 'references' => $references];
                if ($execute) {
                    if ($references !== []) {
                        throw new RuntimeException('Cleanup refused: manifest-owned accounts have business references.');
                    }
                    $before = $this->businessFingerprint($manifest['entries']);
                    DB::table('sessions')->whereIn('user_id', $ids)->delete();
                    DB::table('bfp_personnel_details')->whereIn('user_id', $ids)->delete();
                    DB::table('users')->whereIn('user_id', $ids)->delete();
                    if ($before !== $this->businessFingerprint($manifest['entries'])) {
                        throw new RuntimeException('Cleanup changed unrelated data; rolling back.');
                    }
                    $this->verifyWriteApproval();
                }

                return $result;
            });
            if ($execute) {
                $manifest['cleaned_at'] = now()->toIso8601String();
                $this->saveManifest($manifest);
                $this->writeCsv([]);
            }

            return $result;
        });
    }

    /** @param list<array{email: string, password: string, marker: string, user_id: int|null}> $entries */
    public function writeCsv(array $entries): void
    {
        $path = base_path('jmeter/private/'.self::CSV);
        $temporary = base_path('jmeter/private/accounts.'.bin2hex(random_bytes(8)).'.tmp');
        if (is_link($path)) {
            throw new RuntimeException('Refusing symbolic-link CSV target.');
        }
        $file = @fopen($temporary, 'x');
        if ($file === false) {
            throw new RuntimeException('Cannot create private CSV.');
        }
        try {
            @chmod($temporary, 0600);
            if (fputcsv($file, ['email', 'password'], ',', '"', '') === false) {
                throw new RuntimeException('Cannot write private CSV.');
            }
            foreach ($entries as $entry) {
                if (fputcsv($file, [$entry['email'], $entry['password']], ',', '"', '') === false) {
                    throw new RuntimeException('Cannot write private CSV.');
                }
            }
            if (! fflush($file)) {
                throw new RuntimeException('Cannot flush private CSV.');
            }
        } finally {
            fclose($file);
        }
        if (! @rename($temporary, $path)) {
            throw new RuntimeException('Cannot replace private CSV. Preserve the manifest and retry.');
        }
    }
}
