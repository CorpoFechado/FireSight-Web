<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert any empty strings to NULL so they do not collide under the unique constraint
        DB::statement("UPDATE users SET contact_number = NULL WHERE contact_number = ''");

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('contact_number', 'users_contact_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_contact_number_unique');
        });
    }
};
