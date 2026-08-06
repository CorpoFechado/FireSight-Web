<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bfp_personnel_details', function (Blueprint $table) {
            $table->id('details_id');
            $table->foreignId('user_id')->unique()->constrained('users', 'id')->cascadeOnDelete();
            $table->string('rank', 100);
            $table->string('station_assigned', 150)->default('BFP Lian Fire Station');
            $table->string('employee_number', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bfp_personnel_details');
    }
};
