<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_report', function (Blueprint $table) {
            $table->id('report_id');
            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('reporter_name', 150);
            $table->string('contact_number', 20);
            $table->text('description')->nullable();
            $table->string('report_image')->nullable()->comment('Storage disk path, not a raw blob');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('status', ['pending', 'verified', 'rejected', 'dispatched', 'resolved'])
                ->default('pending');
            $table->timestamp('created_at')->useCurrent();

            $table->index('status', 'idx_report_status');
            $table->index(['latitude', 'longitude'], 'idx_report_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_report');
    }
};
