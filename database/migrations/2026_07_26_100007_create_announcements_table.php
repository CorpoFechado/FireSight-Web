<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement', function (Blueprint $table) {
            $table->id('announcement_id');
            $table->foreignId('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('content');
            $table->enum('announcement_type', ['general', 'advisory', 'emergency', 'fire_safety_tip'])
                ->default('general');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement');
    }
};
