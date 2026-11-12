<?php

use App\Support\JmeterTestAccounts;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    app()->instance('env', 'local');
});

afterEach(function (): void {
    putenv('FIRESIGHT_JMETER_APPROVED_DB');
    putenv('FIRESIGHT_JMETER_PRIVATE_DIR_READY');
});

test('rejects another database even with explicit approval', function (): void {
    putenv('FIRESIGHT_JMETER_APPROVED_DB=firesight_db2');
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'another_database', 'database.connections.mysql.host' => '127.0.0.1', 'database.connections.mysql.port' => '3306', 'database.connections.mysql.url' => null, 'database.connections.mysql.unix_socket' => '']);

    app(JmeterTestAccounts::class)->verifyTarget();
})->throws(RuntimeException::class, 'Refusing: expected');

test('rejects an unapproved target without connecting', function (): void {
    putenv('FIRESIGHT_JMETER_APPROVED_DB');
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'firesight_db2', 'database.connections.mysql.host' => '127.0.0.1', 'database.connections.mysql.port' => '3306', 'database.connections.mysql.url' => null, 'database.connections.mysql.unix_socket' => '']);

    app(JmeterTestAccounts::class)->verifyTarget();
})->throws(RuntimeException::class, 'Refusing: expected');

test('rejects a remote server and database URL without connecting', function (): void {
    putenv('FIRESIGHT_JMETER_APPROVED_DB=firesight_db2');
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'firesight_db2', 'database.connections.mysql.host' => '192.0.2.1', 'database.connections.mysql.port' => '3306', 'database.connections.mysql.url' => 'mysql://example.invalid/test', 'database.connections.mysql.unix_socket' => '']);

    app(JmeterTestAccounts::class)->verifyTarget();
})->throws(RuntimeException::class, 'Refusing: expected');

test('accepts the approved development target when the connected identity matches', function (): void {
    putenv('FIRESIGHT_JMETER_APPROVED_DB=firesight_db2');
    app()->instance('env', 'local');
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => 'firesight_db2', 'database.connections.mysql.host' => '127.0.0.1', 'database.connections.mysql.port' => '3306', 'database.connections.mysql.url' => null, 'database.connections.mysql.unix_socket' => '']);
    DB::shouldReceive('selectOne')->once()->andReturn((object) ['db_name' => 'firesight_db2', 'db_port' => 3306]);

    app(JmeterTestAccounts::class)->verifyTarget();
    expect(true)->toBeTrue();
});

test('refuses a private manifest without an explicit permissions acknowledgement', function (): void {
    putenv('FIRESIGHT_JMETER_PRIVATE_DIR_READY');

    app(JmeterTestAccounts::class)->manifest(false);
})->throws(RuntimeException::class, 'Prepare and restrict');

test('business inspection scopes metadata to the connected database', function (): void {
    Schema::shouldReceive('getTables')->with('firesight_db2')->once()->andReturn([]);
    $connection = Mockery::mock(MySqlConnection::class);
    $connection->shouldReceive('getDatabaseName')->once()->andReturn('firesight_db2');
    DB::shouldReceive('getDriverName')->once()->andReturn('mysql');
    DB::shouldReceive('connection')->once()->andReturn($connection);

    expect(app(JmeterTestAccounts::class)->businessFingerprint([]))->toBe([]);
});
