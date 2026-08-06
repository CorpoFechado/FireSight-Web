<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extends the starter kit's `users` table with FireSight's business
     * fields. The Fortify-required columns (id, email, password,
     * email_verified_at, two_factor_*, remember_token) are left untouched
     * so registration, 2FA, and passkeys keep working. `name` is dropped
     * in favor of first_name/last_name — the User model exposes a virtual
     * `name` attribute (getter + setter) so every existing Fortify/profile
     * code path that reads or writes `name` keeps working unchanged.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['resident', 'bfp_personnel', 'bfp_admin'])
                ->default('resident')
                ->after('id');
            $table->string('first_name', 100)->after('role');
            $table->string('last_name', 100)->after('first_name');
            $table->string('contact_number', 20)->nullable()->after('email');
            $table->string('username', 50)->unique()->nullable()->after('contact_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'first_name', 'last_name', 'contact_number', 'username']);
        });
    }
};
