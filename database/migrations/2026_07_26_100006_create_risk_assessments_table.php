<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessment', function (Blueprint $table) {
            $table->id('risk_id');
            $table->foreignId('barangay_id')->constrained('barangay', 'barangay_id')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('prediction_score', 5, 4);
            $table->enum('risk_level', ['low', 'moderate', 'high', 'severe']);
            $table->timestamp('generated_at')->useCurrent();

            $table->index(['barangay_id', 'date'], 'idx_risk_barangay_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessment');
    }
};
