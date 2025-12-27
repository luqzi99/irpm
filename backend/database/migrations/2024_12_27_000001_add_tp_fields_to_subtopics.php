<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subtopics', function (Blueprint $table) {
            // tp_descriptions: {"1": "TP1 desc", "2": "TP2 desc", ...}
            $table->json('tp_descriptions')->nullable()->after('description');
            // tp_max: maximum TP level (4 or 6)
            $table->integer('tp_max')->default(6)->after('tp_descriptions');
        });
    }

    public function down(): void
    {
        Schema::table('subtopics', function (Blueprint $table) {
            $table->dropColumn(['tp_descriptions', 'tp_max']);
        });
    }
};
