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
        Schema::create('dskp_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dskp_import_id')->constrained('dskp_imports')->onDelete('cascade');
            $table->integer('row_number')->nullable();
            $table->text('message');
            $table->enum('level', ['info', 'warning', 'error'])->default('info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dskp_import_logs');
    }
};
