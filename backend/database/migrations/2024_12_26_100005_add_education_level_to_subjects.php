<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Add code column if not exists
            if (!Schema::hasColumn('subjects', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            // education_level: tahun_1, tahun_2, ..., tahun_6, tingkatan_1, ..., tingkatan_5
            if (!Schema::hasColumn('subjects', 'education_level')) {
                $table->string('education_level')->default('primary')->after('level');
            }
            if (!Schema::hasColumn('subjects', 'year')) {
                $table->integer('year')->nullable()->after('education_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['code', 'education_level', 'year']);
        });
    }
};
