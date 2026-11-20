<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_cctv', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('name')->default('My Home CCTV');
            $table->string('camera_ip')->nullable();
            $table->unsignedSmallInteger('rtsp_port')->default(554);
            $table->string('stream_path')->default('stream2');
            $table->string('rtsp_username')->nullable();
            $table->string('rtsp_password')->nullable();
            $table->text('custom_rtsp_url')->nullable();
            $table->string('camera_token', 64)->unique();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamp('last_fire_detected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_cctv');
    }
};
