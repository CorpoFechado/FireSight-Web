<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auth_token', function (Blueprint $table) {
            $table->string('push_token', 255)->nullable()->after('device_info');
            $table->index('push_token', 'idx_auth_token_push');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_token', function (Blueprint $table) {
            $table->dropIndex('idx_auth_token_push');
            $table->dropColumn('push_token');
        });
    }
};
