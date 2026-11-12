<?php

use App\Console\Commands\VerifyJmeterEnvironment;
use App\Support\JmeterTestAccounts;
use Tests\TestCase;

uses(TestCase::class);

test('verification refuses the normal application environment before accessing the database', function (): void {
    $accounts = Mockery::mock(JmeterTestAccounts::class);
    $accounts->shouldNotReceive('verifyTarget');

    (new VerifyJmeterEnvironment)->handle($accounts);
})->throws(RuntimeException::class, 'Refusing: expected uncached');
