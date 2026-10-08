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
        Schema::create('afor_report', function (Blueprint $table) {
            $table->id('afor_id');
            $table->foreignId('incident_id')
                ->unique()
                ->constrained('incident_record', 'incident_id')
                ->cascadeOnDelete();

            $table->time('alarm_received_time')->nullable();
            $table->string('location_description', 255)->nullable();
            $table->string('caller_name', 150)->nullable();
            $table->string('caller_office_address', 255)->nullable();
            $table->string('receiving_personnel', 150)->nullable();

            $table->json('engine_responses')->nullable();
            $table->enum('responder_type', [
                'first_responder',
                'augmenting_team',
            ])->nullable();

            $table->dateTime('time_under_control')->nullable();
            $table->dateTime('time_fire_out')->nullable();
            $table->enum('occupancy_type', [
                'structural',
                'non_structural',
                'vehicular',
            ])->nullable();
            $table->string('occupancy_type_detail', 255)->nullable();
            $table->decimal('distance_from_station_km', 6, 2)->nullable();
            $table->text('structure_description')->nullable();

            $table->unsignedInteger('civilian_injured')->default(0);
            $table->unsignedInteger('civilian_death')->default(0);
            $table->unsignedInteger('firefighter_injured')->default(0);
            $table->unsignedInteger('firefighter_death')->default(0);

            $table->json('breathing_apparatus')->nullable();
            $table->enum('alarm_level', [
                '1st_alarm',
                '2nd_alarm',
                '3rd_alarm',
                '4th_alarm',
                '5th_alarm',
                'task_force_alpha',
                'task_force_bravo',
                'task_force_charlie',
                'task_force_delta',
                'task_force_echo',
                'task_force_hotel',
                'task_force_india',
                'general_alarm',
            ])->nullable();
            $table->json('alarm_declarations')->nullable();
            $table->json('extinguishing_agents')->nullable();
            $table->json('ropes_ladders')->nullable();
            $table->json('hose_lines')->nullable();
            $table->json('duty_personnel')->nullable();

            $table->string('sketch_path', 255)->nullable();
            $table->text('narrative')->nullable();
            $table->text('problems_encountered')->nullable();
            $table->text('observations_recommendations')->nullable();

            $table->string('prepared_by_name', 150)->nullable();
            $table->string('noted_by_name', 150)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afor_report');
    }
};
