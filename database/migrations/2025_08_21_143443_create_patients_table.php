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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->integer('age');
            $table->enum('age_group', ['0-5', '6-17', '18-35', '36-55', '56+']);
            $table->enum('economic_status', ['low', 'middle', 'high']);
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->timestamps();

            // Indexes for performance
            $table->index('gender');
            $table->index('age_group');
            $table->index('economic_status');
            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
