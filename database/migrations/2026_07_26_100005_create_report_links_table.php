<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_link', function (Blueprint $table) {
            $table->id('link_id');
            $table->foreignId('main_report_id')->constrained('community_report', 'report_id')->cascadeOnDelete();
            $table->foreignId('related_report_id')->constrained('community_report', 'report_id')->cascadeOnDelete();

            $table->unique(['main_report_id', 'related_report_id'], 'uq_report_link');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_link');
    }
};
