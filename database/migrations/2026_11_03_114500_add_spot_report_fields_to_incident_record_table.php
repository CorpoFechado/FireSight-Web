<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_record', function (Blueprint $table) {
            $table->string('establishment_name', 200)->nullable()->after('notes');
            $table->string('owner_name', 150)->nullable()->after('establishment_name');
            $table->string('occupant_name', 150)->nullable()->after('owner_name');
            $table->dateTime('time_fire_started')->nullable()->after('occupant_name');
            $table->dateTime('time_fire_out')->nullable()->after('time_fire_started');
            $table->unsignedInteger('injuries')->default(0)->after('time_fire_out');
            $table->unsignedInteger('fatalities')->default(0)->after('injuries');
        });
    }

    public function down(): void
    {
        Schema::table('incident_record', function (Blueprint $table) {
            $table->dropColumn([
                'establishment_name',
                'owner_name',
                'occupant_name',
                'time_fire_started',
                'time_fire_out',
                'injuries',
                'fatalities',
            ]);
        });
    }
};
