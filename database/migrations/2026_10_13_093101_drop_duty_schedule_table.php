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
        Schema::dropIfExists('duty_schedule');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('duty_schedule', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->date('duty_date');
            $table->time('time_start');
            $table->time('time_end');
            $table->foreignId('created_by')->constrained('users', 'id');
            $table->timestamps();

            $table->index(['duty_date', 'time_start', 'time_end'], 'idx_duty_window');
            $table->unique(['user_id', 'duty_date', 'time_start'], 'uniq_user_duty_slot');
        });
    }
};
