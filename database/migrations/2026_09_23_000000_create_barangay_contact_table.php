<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barangay-level emergency contacts (captain, kagawad, etc.), so BFP staff
 * can confirm a fire report with the barangay before/while dispatching —
 * part of the manual verification process per the adviser's feedback.
 *
 * Mirrors the `barangay_contact` table already used on the mobile app
 * (firesight_mobile_cs DB). Schema only here — actual contact data is
 * seeded separately by BarangayContactSeeder, matched by barangay_name
 * rather than barangay_id, since the two databases assign different IDs
 * to the same barangay.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_contact', function (Blueprint $table) {
            $table->id('contact_id');
            $table->foreignId('barangay_id')->constrained('barangay', 'barangay_id')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('role', 50);
            $table->string('phone_number', 20);

            $table->index('barangay_id', 'idx_barangay_contact_barangay');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangay_contact');
    }
};
