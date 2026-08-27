<?php

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->upgradeCourses();
        $this->upgradeUnits();
        $this->upgradeLessonsAndContents();
        $this->indexPackages();
    }

    public function down(): void
    {
        if (Schema::hasIndex('subscription_packages', ['course_id', 'name'])) {
            Schema::table('subscription_packages', function (Blueprint $table) {
                $table->dropIndex(['course_id', 'name']);
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

        Schema::dropIfExists('lesson_contents');

        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasIndex('lessons', ['unit_id', 'position'])) {
                $table->dropUnique(['unit_id', 'position']);
            }
            if (Schema::hasColumn('lessons', 'description')) {
                $table->dropColumn(array_values(array_filter([
                    'description',
                    'position',
                    'status',
                    'is_preview',
                    'estimated_duration',
                ], fn (string $column) => Schema::hasColumn('lessons', $column))));
            }
        });

        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasIndex('units', ['course_id', 'position'])) {
                $table->dropUnique(['course_id', 'position']);
            }
        });
    }

    private function upgradeCourses(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (! Schema::hasColumn('courses', 'is_free')) {
                $table->boolean('is_free')->default(false)->after('status');
            }
            if (! Schema::hasColumn('courses', 'reference_price')) {
                $table->decimal('reference_price', 8, 2)->nullable()->after('is_free');
            }
            if (! Schema::hasColumn('courses', 'cover_image') && ! Schema::hasColumn('courses', 'cover_image_path')) {
                $table->string('cover_image')->nullable();
            }
        });

        if (Schema::hasColumn('courses', 'cover_image_path') && ! Schema::hasColumn('courses', 'cover_image')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->renameColumn('cover_image_path', 'cover_image');
            });
        }

        foreach (DB::table('courses')->orderBy('id')->get() as $course) {
            if (filled($course->slug ?? null)) {
                continue;
            }

            DB::table('courses')->where('id', $course->id)->update([
                'slug' => $this->uniqueCourseSlug((string) $course->title, (int) $course->id),
            ]);
        }

        if (! Schema::hasIndex('courses', 'courses_slug_unique')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unique('slug');
            });
        }

        if (! Schema::hasIndex('courses', 'courses_teacher_id_status_index')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index(['teacher_id', 'status']);
            });
        }
    }

    private function upgradeUnits(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('units', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('description');
            }
            if (! Schema::hasColumn('units', 'status')) {
                $table->string('status')->default(ContentStatus::Live->value);
            }
        });

        foreach (DB::table('units')->orderBy('id')->get() as $unit) {
            $position = Schema::hasColumn('units', 'order_index')
                ? (int) ($unit->order_index ?? 0)
                : (int) ($unit->position ?? 0);

            DB::table('units')->where('id', $unit->id)->update([
                'position' => $position,
                'status' => $unit->status ?: ContentStatus::Live->value,
            ]);
        }

        $this->resequence('units', 'course_id');

        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'order_index')) {
                $table->dropColumn('order_index');
            }
        });

        if (! Schema::hasIndex('units', 'units_status_index')) {
            Schema::table('units', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (! Schema::hasIndex('units', 'units_course_id_position_unique')) {
            Schema::table('units', function (Blueprint $table) {
                $table->unique(['course_id', 'position']);
            });
        }
    }

    private function upgradeLessonsAndContents(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('lessons', 'position')) {
                $table->unsignedInteger('position')->default(0);
            }
            if (! Schema::hasColumn('lessons', 'status')) {
                $table->string('status')->default(ContentStatus::Live->value);
            }
            if (! Schema::hasColumn('lessons', 'is_preview')) {
                $table->boolean('is_preview')->default(false);
            }
            if (! Schema::hasColumn('lessons', 'estimated_duration')) {
                $table->unsignedInteger('estimated_duration')->nullable();
            }
        });

        foreach (DB::table('lessons')->orderBy('id')->get() as $lesson) {
            $position = Schema::hasColumn('lessons', 'order_index')
                ? (int) ($lesson->order_index ?? 0)
                : (int) ($lesson->position ?? 0);

            $preview = Schema::hasColumn('lessons', 'is_free_preview')
                ? (bool) $lesson->is_free_preview
                : (bool) ($lesson->is_preview ?? false);

            $duration = Schema::hasColumn('lessons', 'duration_minutes')
                ? $lesson->duration_minutes
                : ($lesson->estimated_duration ?? null);

            DB::table('lessons')->where('id', $lesson->id)->update([
                'position' => $position,
                'status' => $lesson->status ?: ContentStatus::Live->value,
                'is_preview' => $preview,
                'estimated_duration' => $duration,
            ]);
        }

        $this->resequence('lessons', 'unit_id');

        if (! Schema::hasTable('lesson_contents')) {
            Schema::create('lesson_contents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('title')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->json('data')->nullable();
                $table->string('status')->default(ContentStatus::Live->value);
                $table->timestamps();

                $table->index(['lesson_id', 'type']);
                $table->index('status');
                $table->unique(['lesson_id', 'position']);
            });
        }

        $this->migrateLegacyLessonMedia();

        $legacyLessonColumns = array_values(array_filter(
            ['content_type', 'video_path', 'video_url', 'external_url', 'order_index', 'duration_minutes', 'is_free_preview'],
            fn (string $column) => Schema::hasColumn('lessons', $column),
        ));

        if ($legacyLessonColumns !== []) {
            Schema::table('lessons', function (Blueprint $table) use ($legacyLessonColumns) {
                $table->dropColumn($legacyLessonColumns);
            });
        }

        if (! Schema::hasIndex('lessons', 'lessons_status_index')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (! Schema::hasIndex('lessons', 'lessons_unit_id_position_unique')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->unique(['unit_id', 'position']);
            });
        }

        Schema::dropIfExists('lesson_attachments');
    }

    private function indexPackages(): void
    {
        if (! Schema::hasIndex('subscription_packages', 'subscription_packages_course_id_name_index')) {
            Schema::table('subscription_packages', function (Blueprint $table) {
                $table->index(['course_id', 'name']);
            });
        }
    }

    private function uniqueCourseSlug(string $title, int $courseId): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'course-'.$courseId;
        }

        $slug = $base;
        $suffix = 1;

        while (
            DB::table('courses')
                ->where('slug', $slug)
                ->where('id', '!=', $courseId)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function resequence(string $table, string $parentColumn): void
    {
        $rows = DB::table($table)->orderBy($parentColumn)->orderBy('id')->get();
        $counters = [];

        foreach ($rows as $row) {
            $parentId = (int) $row->{$parentColumn};
            $counters[$parentId] = ($counters[$parentId] ?? 0) + 1;

            DB::table($table)->where('id', $row->id)->update([
                'position' => $counters[$parentId],
            ]);
        }
    }

    private function migrateLegacyLessonMedia(): void
    {
        $now = now();
        $hasVideoPath = Schema::hasColumn('lessons', 'video_path');
        $hasExternalUrl = Schema::hasColumn('lessons', 'external_url');
        $hasVideoUrl = Schema::hasColumn('lessons', 'video_url');
        $hasAttachments = Schema::hasTable('lesson_attachments');

        foreach (DB::table('lessons')->orderBy('id')->get() as $lesson) {
            $position = (int) DB::table('lesson_contents')->where('lesson_id', $lesson->id)->max('position');

            if ($hasVideoPath && filled($lesson->video_path ?? null)) {
                $position++;
                $this->insertContent($lesson->id, 'video', $position, ['path' => $lesson->video_path, 'source' => 'upload'], $now);
            }

            $link = null;
            if ($hasExternalUrl && filled($lesson->external_url ?? null)) {
                $link = $lesson->external_url;
            } elseif ($hasVideoUrl && filled($lesson->video_url ?? null)) {
                $link = $lesson->video_url;
            }

            if ($link) {
                $position++;
                $this->insertContent($lesson->id, 'link', $position, ['url' => $link], $now);
            }
        }

        if (! $hasAttachments) {
            return;
        }

        foreach (DB::table('lesson_attachments')->orderBy('id')->get() as $attachment) {
            $nextPosition = (int) DB::table('lesson_contents')
                ->where('lesson_id', $attachment->lesson_id)
                ->max('position') + 1;

            $this->insertContent(
                (int) $attachment->lesson_id,
                'file',
                max($nextPosition, 1),
                ['name' => $attachment->name, 'path' => $attachment->path],
                $now,
                $attachment->name,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function insertContent(int $lessonId, string $type, int $position, array $data, mixed $now, ?string $title = null): void
    {
        DB::table('lesson_contents')->insert([
            'lesson_id' => $lessonId,
            'type' => $type,
            'title' => $title,
            'position' => $position,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => ContentStatus::Live->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
