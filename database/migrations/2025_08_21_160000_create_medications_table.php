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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('category', [
                'analgesic', 'antibiotic', 'antidiabetic', 'antihypertensive', 
                'antihistamine', 'antacid', 'vitamin', 'hormone', 'other'
            ])->default('other');
            $table->enum('dosage_form', [
                'tablet', 'capsule', 'syrup', 'injection', 'cream', 'drops', 'inhaler', 'other'
            ])->default('tablet');
            $table->string('strength')->nullable(); // e.g., "500mg", "10ml"
            $table->string('manufacturer')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('dosage_form');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
