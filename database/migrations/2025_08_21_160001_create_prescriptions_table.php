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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('prescribed_date');
            $table->integer('quantity'); // Number of units prescribed
            $table->string('dosage'); // e.g., "1 tablet twice daily"
            $table->integer('duration_days')->nullable(); // Treatment duration
            $table->enum('status', ['active', 'completed', 'discontinued'])->default('active');
            $table->string('prescribed_by')->nullable(); // Doctor/prescriber name
            $table->timestamps();

            // Indexes for performance
            $table->index('medication_id');
            $table->index('patient_id');
            $table->index('prescribed_date');
            $table->index('status');
            $table->index(['medication_id', 'prescribed_date']); // Composite index for analytics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
