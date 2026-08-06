<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('message');
            $table->enum('notification_type', ['incident_alert', 'status_update', 'system', 'reminder'])
                ->default('system');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_read'], 'idx_notification_user_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
