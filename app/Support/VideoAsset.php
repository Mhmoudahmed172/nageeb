<?php

namespace App\Support;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Lesson;
use App\Models\LessonContent;
use Illuminate\Http\UploadedFile;

final class VideoAsset
{
    public const STATE_UPLOADING = 'uploading';

    public const STATE_UPLOADED = 'uploaded';

    public const STATE_PROCESSING = 'processing';

    public const STATE_READY = 'ready';

    public const STATE_FAILED = 'failed';

    public static function store(Lesson $lesson, UploadedFile $file, int $position): LessonContent
    {
        $stored = app(MediaStore::class)->store($file, MediaStore::KIND_VIDEO);

        return $lesson->contents()->create([
            'type' => LessonContentType::Video,
            'title' => $stored['original_name'],
            'position' => $position,
            'status' => ContentStatus::Draft,
            'data' => [
                ...$stored,
                'name' => $stored['original_name'],
                'source' => 'upload',
                'upload_status' => self::STATE_UPLOADED,
                'processing_status' => self::STATE_READY,
                'state' => self::STATE_READY,
                'duration' => null,
            ],
        ]);
    }

    public static function storeFile(Lesson $lesson, UploadedFile $file, int $position, LessonContentType $type): LessonContent
    {
        $kind = $type === LessonContentType::Audio ? MediaStore::KIND_AUDIO : MediaStore::KIND_FILE;
        $stored = app(MediaStore::class)->store($file, $kind);

        return $lesson->contents()->create([
            'type' => $type,
            'title' => $stored['original_name'],
            'position' => $position,
            'status' => ContentStatus::Draft,
            'data' => [
                ...$stored,
                'name' => $stored['original_name'],
                'source' => 'upload',
                'upload_status' => self::STATE_UPLOADED,
                'processing_status' => self::STATE_READY,
                'state' => self::STATE_READY,
            ],
        ]);
    }

    public static function state(LessonContent $content): string
    {
        return (string) ($content->data['state'] ?? $content->data['processing_status'] ?? self::STATE_UPLOADED);
    }

    public static function stateLabel(LessonContent $content): string
    {
        return match (self::state($content)) {
            self::STATE_UPLOADING => 'جارٍ الرفع',
            self::STATE_PROCESSING => 'جارٍ المعالجة',
            self::STATE_READY => 'جاهز',
            self::STATE_FAILED => 'فشل',
            default => 'مرفوع',
        };
    }

    public static function durationLabel(LessonContent $content): ?string
    {
        $seconds = $content->data['duration'] ?? null;

        if (! is_numeric($seconds)) {
            return null;
        }

        $seconds = (int) $seconds;
        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $rest);
    }
}
