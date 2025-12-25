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
        Schema::create('comment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_method_id')->constrained('assessment_methods')->onDelete('cascade');
            $table->tinyInteger('tp'); // 1-6
            $table->text('template_text');
            $table->timestamps();
            
            $table->unique(['assessment_method_id', 'tp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_templates');
    }
};
