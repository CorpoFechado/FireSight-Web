<?php

use App\Support\JmeterTestAccounts;
use Database\Seeders\JmeterTestAdminSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

class MemoryJmeterAccounts extends JmeterTestAccounts
{
    public int $checks = 0;

    public bool $failSecondCheck = false;

    public function verifyTarget(): void
    {
        if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('Lifecycle tests require SQLite memory only.');
        }
    }

    public function verifyWriteApproval(): void
    {
        $this->verifyTarget();
        $this->checks++;
        if ($this->failSecondCheck && $this->checks === 2) {
            throw new RuntimeException('Simulated new database log error.');
        }
    }
}

beforeEach(function (): void {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
    config(['hashing.bcrypt.rounds' => 4]);
    DB::purge('sqlite');
    $this->temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'firesight-jmeter-unit-'.bin2hex(random_bytes(8));
    mkdir($this->temporaryDirectory.'/jmeter/private', 0700, true);
    app()->setBasePath($this->temporaryDirectory);
    putenv('FIRESIGHT_JMETER_PRIVATE_DIR_READY=1');
    putenv('FIRESIGHT_JMETER_ACCOUNT_COUNT=50');
    Schema::create('users', function (Blueprint $table): void {
        $table->id('user_id');
        $table->string('role');
        $table->string('status');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamp('email_verified_at')->nullable();
        $table->boolean('is_verified')->default(false);
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->timestamp('two_factor_confirmed_at')->nullable();
        $table->timestamps();
    });
    Schema::create('bfp_personnel_details', function (Blueprint $table): void {
        $table->id('details_id');
        $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->cascadeOnDelete();
        $table->string('rank');
        $table->string('station_assigned');
        $table->string('employee_number')->unique();
        $table->string('duty_group')->nullable();
    });
    Schema::create('sessions', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable();
    });
    Schema::create('community_report', function (Blueprint $table): void {
        $table->id('report_id');
        $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
        $table->string('description');
    });
    $this->realUser = DB::table('users')->insertGetId(['role' => 'bfp_admin', 'status' => 'active', 'first_name' => 'Existing', 'last_name' => 'Fixture', 'email' => 'existing@example.test', 'password' => Hash::make('existing-fixture'), 'is_verified' => true], 'user_id');
    DB::table('community_report')->insert(['user_id' => $this->realUser, 'description' => 'existing fixture report']);
    $this->accounts = new MemoryJmeterAccounts;
    app()->instance(JmeterTestAccounts::class, $this->accounts);
});

afterEach(function (): void {
    DB::disconnect('sqlite');
    putenv('FIRESIGHT_JMETER_PRIVATE_DIR_READY');
    putenv('FIRESIGHT_JMETER_ACCOUNT_COUNT');
    $resolved = realpath($this->temporaryDirectory);
    $root = realpath(sys_get_temp_dir());
    if ($resolved !== false && $root !== false && str_starts_with($resolved, $root.DIRECTORY_SEPARATOR.'firesight-jmeter-unit-')) {
        (new Filesystem)->deleteDirectory($resolved);
    }
});

test('provisions fifty unique identities, records exact IDs and is idempotent', function (): void {
    $before = DB::table('users')->where('user_id', $this->realUser)->first();
    (new JmeterTestAdminSeeder)->run();
    $first = $this->accounts->manifest(false);
    (new JmeterTestAdminSeeder)->run();
    expect(DB::table('users')->count())->toBe(51)
        ->and(DB::table('bfp_personnel_details')->count())->toBe(50)
        ->and(DB::table('users')->where('user_id', $this->realUser)->first())->toEqual($before);
    $second = $this->accounts->manifest(false);
    expect(array_column($second['entries'], 'user_id'))->toBe(array_column($first['entries'], 'user_id'));
    $csv = array_map('str_getcsv', file(base_path('jmeter/private/accounts.csv'), FILE_IGNORE_NEW_LINES));
    expect($csv[0])->toBe(['email', 'password'])->and(count($csv))->toBe(51)
        ->and(count(array_unique(array_column(array_slice($csv, 1), 0))))->toBe(50);
    foreach ($second['entries'] as $entry) {
        expect($this->accounts->ownedUser($entry)->user_id)->toBe($entry['user_id']);
    }
});

test('extends and resumes two hundred accounts while preserving original users and manifest entries', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $original = $this->accounts->manifest(false)['entries'];
    $users = DB::table('users')->orderBy('user_id')->get()->all();
    $personnel = DB::table('bfp_personnel_details')->orderBy('user_id')->get()->all();
    putenv('FIRESIGHT_JMETER_ACCOUNT_COUNT=200');
    $this->accounts->checks = 0;
    $this->accounts->failSecondCheck = true;
    expect(fn () => (new JmeterTestAdminSeeder)->run())->toThrow(RuntimeException::class, 'Simulated new database log error');
    $checkpoint = $this->accounts->manifest(false);
    expect(count($checkpoint['entries']))->toBe(200)
        ->and(array_slice($checkpoint['entries'], 0, 50))->toBe($original)
        ->and(DB::table('users')->orderBy('user_id')->get()->all())->toEqual($users)
        ->and(DB::table('bfp_personnel_details')->orderBy('user_id')->get()->all())->toEqual($personnel);
    $this->accounts->failSecondCheck = false;
    (new JmeterTestAdminSeeder)->run();
    (new JmeterTestAdminSeeder)->run();
    $extended = $this->accounts->manifest(false);
    expect(array_slice($extended['entries'], 0, 50))->toBe($original)
        ->and(DB::table('users')->whereIn('user_id', array_column($original, 'user_id'))->orderBy('user_id')->get()->all())->toEqual(array_slice($users, 1))
        ->and(DB::table('bfp_personnel_details')->whereIn('user_id', array_column($original, 'user_id'))->orderBy('user_id')->get()->all())->toEqual($personnel)
        ->and(DB::table('users')->count())->toBe(201)
        ->and(DB::table('bfp_personnel_details')->count())->toBe(200)
        ->and($this->accounts->cleanup(false)['accounts'])->toBe(200);
    $csv = array_map('str_getcsv', file(base_path('jmeter/private/accounts.csv'), FILE_IGNORE_NEW_LINES));
    expect(count($csv))->toBe(201)
        ->and(count(array_unique(array_column(array_slice($csv, 1), 0))))->toBe(200);
});

test('extension refuses a missing original account', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $entry = $this->accounts->manifest(false)['entries'][0];
    DB::table('bfp_personnel_details')->where('user_id', $entry['user_id'])->delete();
    DB::table('users')->where('user_id', $entry['user_id'])->delete();
    putenv('FIRESIGHT_JMETER_ACCOUNT_COUNT=200');
    expect(fn () => (new JmeterTestAdminSeeder)->run())->toThrow(RuntimeException::class, 'previously recorded account is missing');
    expect(count($this->accounts->manifest(false)['entries']))->toBe(50);
});

test('refuses email collisions without overwriting existing accounts', function (): void {
    DB::table('users')->insert(['role' => 'resident', 'status' => 'active', 'first_name' => 'Collision', 'last_name' => 'Fixture', 'email' => 'testadmin01@example.test', 'password' => 'unchanged']);
    expect(fn () => (new JmeterTestAdminSeeder)->run())->toThrow(RuntimeException::class, 'collision');
    expect(DB::table('users')->count())->toBe(2)->and(DB::table('bfp_personnel_details')->count())->toBe(0);
});

test('cleanup dry run does not delete and business references prevent execution', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $id = $this->accounts->manifest(false)['entries'][0]['user_id'];
    DB::table('community_report')->insert(['user_id' => $id, 'description' => 'must survive']);
    $preview = $this->accounts->cleanup(false);
    expect($preview['accounts'])->toBe(50)->and($preview['references']['community_report.user_id'])->toBe(1);
    expect(fn () => $this->accounts->cleanup(true))->toThrow(RuntimeException::class, 'business references');
    expect(DB::table('users')->count())->toBe(51)->and(DB::table('community_report')->count())->toBe(2);
});

test('cleanup removes only owned users, personnel and sessions and can be repeated', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $id = $this->accounts->manifest(false)['entries'][0]['user_id'];
    DB::table('sessions')->insert([['id' => 'test-session', 'user_id' => $id], ['id' => 'real-session', 'user_id' => $this->realUser]]);
    expect($this->accounts->cleanup(false)['accounts'])->toBe(50);
    expect(DB::table('users')->count())->toBe(51);
    expect($this->accounts->cleanup(true)['accounts'])->toBe(50)
        ->and(DB::table('users')->count())->toBe(1)
        ->and(DB::table('bfp_personnel_details')->count())->toBe(0)
        ->and(DB::table('community_report')->count())->toBe(1)
        ->and(DB::table('sessions')->pluck('id')->all())->toBe(['real-session']);
    expect($this->accounts->cleanup(true)['accounts'])->toBe(0);
});

test('a new log failure rolls back provisioning', function (): void {
    $this->accounts->failSecondCheck = true;
    expect(fn () => (new JmeterTestAdminSeeder)->run())->toThrow(RuntimeException::class, 'Simulated new database log error');
    expect(DB::table('users')->count())->toBe(1)->and(DB::table('bfp_personnel_details')->count())->toBe(0);
});

test('cleanup refuses a manifest whose user ID was replaced', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $manifest = $this->accounts->manifest(false);
    $manifest['entries'][0]['user_id'] = $this->realUser;
    $this->accounts->saveManifest($manifest);
    expect(fn () => $this->accounts->cleanup(true))->toThrow(RuntimeException::class, 'ownership mismatch');
    expect(DB::table('users')->count())->toBe(51);
});

test('cleanup command defaults to preview and refuses unconfirmed deletion', function (): void {
    (new JmeterTestAdminSeeder)->run();
    $this->artisan('jmeter:cleanup-test-accounts')->assertSuccessful();
    expect(DB::table('users')->count())->toBe(51);
    $this->artisan('jmeter:cleanup-test-accounts', ['--execute' => true])->assertFailed();
    expect(DB::table('users')->count())->toBe(51);
});
