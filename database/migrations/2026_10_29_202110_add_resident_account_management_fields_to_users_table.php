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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->timestamp('details_updated_at')->nullable()->after('password_changed_at');
            $table->timestamp('suspended_at')->nullable()->after('details_updated_at');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn([
                'password_changed_at',
                'details_updated_at',
                'suspended_at',
                'suspension_reason',
            ]);
        });
    }
};
