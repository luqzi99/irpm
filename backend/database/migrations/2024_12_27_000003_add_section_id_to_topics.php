<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add section_id to topics table
        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('id')->constrained('sections')->onDelete('cascade');
        });
        
        // Migrate existing data: create sections from topics.theme field
        $topics = DB::table('topics')->whereNotNull('theme')->get();
        $sectionMap = [];
        
        foreach ($topics as $topic) {
            $theme = $topic->theme ?? '';
            $parts = explode(' > ', $theme);
            $themeName = $parts[0] ?? 'Default';
            $titleFull = $parts[1] ?? $themeName;
            
            // Parse title_code and title_name from "1.0 KEMAHIRAN"
            preg_match('/^([\d.]+)\s+(.+)$/', $titleFull, $matches);
            $titleCode = $matches[1] ?? '0.0';
            $titleName = $matches[2] ?? $titleFull;
            
            // Get academic year from environment or default to current year
            $academicYear = date('Y');
            
            $key = "{$topic->subject_id}_{$academicYear}_{$titleCode}";
            
            if (!isset($sectionMap[$key])) {
                $sectionId = DB::table('sections')->insertGetId([
                    'subject_id' => $topic->subject_id,
                    'academic_year' => $academicYear,
                    'theme' => $themeName,
                    'title_code' => $titleCode,
                    'title_name' => $titleName,
                    'sequence' => count($sectionMap) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sectionMap[$key] = $sectionId;
            }
            
            // Update topic with section_id
            DB::table('topics')->where('id', $topic->id)->update([
                'section_id' => $sectionMap[$key],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
};
