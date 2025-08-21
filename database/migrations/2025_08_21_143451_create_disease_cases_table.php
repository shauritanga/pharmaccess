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
        Schema::create('disease_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disease_id')->constrained('diseases')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('reported_date');
            $table->enum('status', ['active', 'recovered', 'deceased'])->default('active');
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('mild');
            $table->timestamps();

            // Indexes for performance
            $table->index('disease_id');
            $table->index('patient_id');
            $table->index('reported_date');
            $table->index('status');
            $table->index(['disease_id', 'reported_date']); // Composite index for common queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_cases');
    }
};
