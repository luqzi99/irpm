<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->year('academic_year'); // 2024, 2025, etc.
            $table->string('theme'); // e.g., "SAINS HAYAT", "INKUIRI DALAM SAINS"
            $table->string('title_code'); // e.g., "1.0", "2.0"
            $table->string('title_name'); // e.g., "KEMAHIRAN SAINTIFIK", "MANUSIA"
            $table->integer('sequence')->default(0);
            $table->timestamps();
            
            // Unique constraint: one section per subject/year/title_code
            $table->unique(['subject_id', 'academic_year', 'title_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
