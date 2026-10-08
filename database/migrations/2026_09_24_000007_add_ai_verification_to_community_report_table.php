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
        Schema::table('community_report', function (Blueprint $table) {
            if (! Schema::hasColumn('community_report', 'ai_fire_label')) {
                $table->string('ai_fire_label', 20)->nullable()->after('report_image');
            }

            if (! Schema::hasColumn('community_report', 'ai_fire_confidence')) {
                $table->decimal('ai_fire_confidence', 5, 4)->nullable()->after('ai_fire_label');
            }

            if (! Schema::hasColumn('community_report', 'ai_verified_at')) {
                $table->dateTime('ai_verified_at')->nullable()->after('ai_fire_confidence');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_report', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('community_report', 'ai_verified_at')) {
                $columns[] = 'ai_verified_at';
            }
            if (Schema::hasColumn('community_report', 'ai_fire_confidence')) {
                $columns[] = 'ai_fire_confidence';
            }
            if (Schema::hasColumn('community_report', 'ai_fire_label')) {
                $columns[] = 'ai_fire_label';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
