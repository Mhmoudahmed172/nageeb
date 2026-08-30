<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\LessonContent;
use App\Support\ContentAccess;
use App\Support\ExamAccess;
use App\Support\MediaStore;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProtectedMediaController extends Controller
{
    public function __construct(
        private readonly ContentAccess $contentAccess,
        private readonly ExamAccess $examAccess,
        private readonly MediaStore $media,
    ) {}

    public function lessonContent(LessonContent $content): StreamedResponse
    {
        $content->load(['lesson.unit.semester.course', 'regions']);

        $this->authorizeLessonContent($content);

        $path = (string) ($content->data['path'] ?? '');
        $disk = $content->data['disk'] ?? null;
        $name = (string) ($content->data['original_name'] ?? $content->data['name'] ?? $content->displayName());
        $mime = (string) ($content->data['mime'] ?? 'application/octet-stream');
        $inline = $content->type->value === 'video' || $content->type->value === 'audio';

        return $this->media->response($path, $name, $mime, $inline, $disk);
    }

    public function examPaper(Exam $exam): StreamedResponse
    {
        abort_unless($exam->hasPaperFile(), 404);

        $this->authorizeExamPaper($exam);

        return $this->media->response(
            (string) $exam->file_path,
            (string) ($exam->file_original_name ?: $exam->title),
            (string) ($exam->file_mime ?: 'application/octet-stream'),
            true,
            $exam->file_disk,
        );
    }

    private function authorizeLessonContent(LessonContent $content): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 401);

        $course = $content->lesson?->unit?->semester?->course;
        abort_unless($course !== null, 404);

        if ($user->isTeacher() || $user->isAdmin()) {
            abort_unless($user->can('viewLessonContent', [$course, $content]), 403);

            return;
        }

        abort_unless($this->contentAccess->studentCanAccessContent($user, $content), 403);
    }

    private function authorizeExamPaper(Exam $exam): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 401);

        if ($user->isTeacher() || $user->isAdmin()) {
            abort_unless($user->can('view', $exam), 403);

            return;
        }

        abort_unless($this->examAccess->studentCanAccessExam($user, $exam), 403);
    }
}
