<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates six tables from the mobile database that did not exist in the
 * web database: auth_token, report_evidence, report_status_history,
 * emergency_contact, fire_education_content, resident_address.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. auth_token ─────────────────────────────────────────────────────
        Schema::create('auth_token', function (Blueprint $table) {
            $table->id('token_id');
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();
            $table->string('token', 255)->unique();
            $table->string('device_info', 255)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_auth_token_user');
        });

        // 2. report_evidence ────────────────────────────────────────────────
        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id('evidence_id');
            $table->foreignId('report_id')
                ->constrained('community_report', 'report_id')
                ->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users', 'id')
                ->nullOnDelete();
            $table->string('caption', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('report_id', 'idx_evidence_report');
        });

        // 3. report_status_history ──────────────────────────────────────────
        Schema::create('report_status_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('report_id')
                ->constrained('community_report', 'report_id')
                ->cascadeOnDelete();
            $table->enum('status', [
                'pending', 'verified', 'accepted',
                'dispatched', 'resolved', 'completed',
                'rejected', 'invalid',
            ])->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users', 'id')
                ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('report_id', 'idx_history_report');
        });

        // 4. emergency_contact ──────────────────────────────────────────────
        Schema::create('emergency_contact', function (Blueprint $table) {
            $table->id('contact_id');
            $table->string('name', 150)->nullable();
            $table->enum('category', ['fire', 'police', 'medical', 'disaster', 'other'])
                ->default('other');
            $table->string('phone_number', 20)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_primary')->default(false)
                ->comment('Shown in the top "In Case of Fire" quick-dial card');
            $table->integer('sort_order')->default(0);
        });

        // 5. fire_education_content ─────────────────────────────────────────
        Schema::create('fire_education_content', function (Blueprint $table) {
            $table->id('content_id');
            $table->string('title', 255)->nullable();
            $table->enum('category', ['prevention', 'emergency_response', 'awareness'])
                ->default('prevention');
            $table->string('summary', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->integer('read_minutes')->default(3);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 6. resident_address ───────────────────────────────────────────────
        Schema::create('resident_address', function (Blueprint $table) {
            $table->id('address_id');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'id')
                ->nullOnDelete();
            $table->string('house_no_street', 150)->nullable();
            $table->foreignId('barangay_id')
                ->nullable()
                ->constrained('barangay', 'barangay_id')
                ->nullOnDelete();
            $table->string('municipality', 100)->default('Lian');
            $table->string('province', 100)->default('Batangas');

            $table->index('user_id', 'idx_address_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_address');
        Schema::dropIfExists('fire_education_content');
        Schema::dropIfExists('emergency_contact');
        Schema::dropIfExists('report_status_history');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('auth_token');
    }
};
