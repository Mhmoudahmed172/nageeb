<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'last_viewed_lesson_id')) {
                $table->foreignId('last_viewed_lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'last_viewed_lesson_id')) {
                $table->dropConstrainedForeignId('last_viewed_lesson_id');
            }
        });
    }
};
