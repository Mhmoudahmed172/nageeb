<?php

use App\Enums\ExamStatus;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default(QuestionType::MultipleChoice->value);
            $table->text('text');
            $table->text('explanation')->nullable();
            $table->decimal('points', 6, 2)->default(1);
            $table->string('difficulty')->default(QuestionDifficulty::Medium->value);
            // Reserved for question types that need structured payloads (matching, ordering…).
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'type']);
            $table->index(['teacher_id', 'difficulty']);
            $table->index('course_id');
            $table->index('unit_id');
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'position']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('max_attempts')->default(1);
            $table->decimal('passing_score', 5, 2)->default(50);
            $table->string('status')->default(ExamStatus::Draft->value);
            $table->boolean('show_results_immediately')->default(true);
            $table->boolean('show_correct_answers')->default(false);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index('lesson_id');
        });

        // Mirrors lesson_content_region: no rows means the exam is open to every region.
        Schema::create('exam_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'region_id']);
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            // Null keeps the question bank value; a value overrides it for this exam only.
            $table->decimal('points', 6, 2)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
            $table->index(['exam_id', 'position']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('status')->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            // Snapshot of the grade at submission time so later exam edits cannot rewrite history.
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('text_answer')->nullable();
            // Null means "not graded yet", which future manual-grading types will use.
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_region');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
