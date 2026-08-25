<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->unsignedInteger('order_index')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lessons')) {
            Schema::create('lessons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('content_type')->default('uploaded_video');
                $table->string('video_path')->nullable();
                $table->string('external_url')->nullable();
                $table->unsignedInteger('order_index')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lesson_attachments')) {
            Schema::create('lesson_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('path');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_attachments');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('units');
    }
};
