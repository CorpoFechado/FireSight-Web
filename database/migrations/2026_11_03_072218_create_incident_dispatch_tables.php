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
        // 1. Primary dispatch event record
        Schema::create('incident_dispatch', function (Blueprint $table) {
            $table->id('dispatch_id');
            $table->foreignId('report_id')
                ->constrained('community_report', 'report_id')
                ->cascadeOnDelete();
            $table->foreignId('dispatched_by')
                ->constrained('users', 'user_id');
            $table->timestamp('dispatched_at')->useCurrent();

            $table->index('report_id', 'idx_dispatch_report');
            $table->index('dispatched_at', 'idx_dispatch_time');
        });

        // 2. Relational fire trucks deployed per dispatch event
        Schema::create('incident_dispatch_truck', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')
                ->constrained('incident_dispatch', 'dispatch_id')
                ->cascadeOnDelete();
            $table->string('truck_name', 50);

            $table->index('dispatch_id', 'idx_dispatch_truck_dispatch');
        });

        // 3. Relational personnel deployed per dispatch event
        Schema::create('incident_dispatch_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')
                ->constrained('incident_dispatch', 'dispatch_id')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->cascadeOnDelete();

            $table->index('dispatch_id', 'idx_dispatch_personnel_dispatch');
            $table->index('user_id', 'idx_dispatch_personnel_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_dispatch_personnel');
        Schema::dropIfExists('incident_dispatch_truck');
        Schema::dropIfExists('incident_dispatch');
    }
};
