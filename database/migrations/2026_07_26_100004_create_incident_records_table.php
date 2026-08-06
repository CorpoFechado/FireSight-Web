<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_record', function (Blueprint $table) {
            $table->id('incident_id');
            $table->foreignId('report_id')->constrained('community_report', 'report_id')->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained('barangay', 'barangay_id');
            $table->dateTime('data_time');
            $table->enum('incident_type', ['structural', 'grass', 'electrical', 'vehicular', 'other']);
            $table->enum('severity_level', ['low', 'moderate', 'high', 'critical']);
            $table->string('cause_of_fire', 150)->nullable();
            $table->unsignedInteger('casualties')->default(0);
            $table->text('notes')->nullable();

            $table->index('barangay_id', 'idx_incident_barangay');
            $table->index('data_time', 'idx_incident_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_record');
    }
};
