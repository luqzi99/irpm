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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade');
            $table->foreignId('subtopic_id')->constrained('subtopics')->onDelete('cascade');
            $table->foreignId('assessment_method_id')->nullable()->constrained('assessment_methods')->onDelete('set null');
            $table->tinyInteger('tp'); // 1-6
            $table->text('auto_comment')->nullable();
            $table->text('custom_comment')->nullable();
            $table->date('evaluation_date');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['student_id', 'subject_id']);
            $table->index(['teacher_id', 'evaluation_date']);
            $table->index('subtopic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
