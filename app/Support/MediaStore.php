<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Private, UUID-named storage for lesson videos, attachments, and exam papers.
 * Default disk is `local` (storage/app/private). Production may set MEDIA_DISK=s3.
 * Every record stores `disk` so existing files keep working after a disk change.
 * Never serve these files from the public /storage symlink.
 */
class MediaStore
{
    public const DISK = 'local';

    public static function defaultDisk(): string
    {
        $disk = (string) config('filesystems.media_disk', self::DISK);

        return $disk !== '' ? $disk : self::DISK;
    }

    public const KIND_VIDEO = 'video';

    public const KIND_AUDIO = 'audio';

    public const KIND_FILE = 'file';

    public const KIND_EXAM = 'exam';

    /**
     * @var array<string, array{directory: string, max_kb: int, extensions: list<string>, mimes: list<string>}>
     */
    private const KINDS = [
        self::KIND_VIDEO => [
            'directory' => 'lessons/videos',
            'max_kb' => 512000,
            'extensions' => ['mp4', 'webm', 'mov', 'm4v', 'avi'],
            'mimes' => [
                'video/mp4',
                'video/webm',
                'video/quicktime',
                'video/x-m4v',
                'video/x-msvideo',
                'video/avi',
                'video/mpeg',
            ],
        ],
        self::KIND_AUDIO => [
            'directory' => 'lessons/audio',
            'max_kb' => 102400,
            'extensions' => ['mp3', 'wav', 'm4a', 'ogg'],
            'mimes' => [
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/x-wav',
                'audio/mp4',
                'audio/x-m4a',
                'audio/ogg',
            ],
        ],
        self::KIND_FILE => [
            'directory' => 'lessons/files',
            'max_kb' => 51200,
            'extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webp', 'zip'],
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/zip',
                'application/x-zip-compressed',
            ],
        ],
        self::KIND_EXAM => [
            'directory' => 'exams/papers',
            'max_kb' => 51200,
            'extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'],
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
        ],
    ];

    /**
     * @var list<string>
     */
    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'pht',
        'cgi', 'exe', 'bat', 'cmd', 'com', 'scr', 'js', 'mjs', 'html', 'htm',
        'shtml', 'svg', 'xhtml', 'sh', 'bash', 'ps1', 'dll', 'so',
    ];

    public function maxKilobytes(string $kind): int
    {
        return self::KINDS[$kind]['max_kb'];
    }

    /**
     * @return list<string>
     */
    public function extensions(string $kind): array
    {
        return self::KINDS[$kind]['extensions'];
    }

    /**
     * Store the file on the private disk and return the metadata to persist.
     *
     * @return array{disk: string, path: string, original_name: string, mime: string, size: int, uploaded_at: string}
     */
    public function store(UploadedFile $file, string $kind): array
    {
        $this->assertSafe($file, $kind);

        $extension = $this->extension($file);
        $path = $file->storeAs(
            self::KINDS[$kind]['directory'],
            Str::uuid()->toString().'.'.$extension,
            self::defaultDisk(),
        );

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'file' => 'حدث خطأ في التخزين. حاول مرة أخرى.',
            ]);
        }

        return [
            'disk' => self::defaultDisk(),
            'path' => $path,
            'original_name' => $this->originalName($file),
            'mime' => $this->detectedMime($file),
            'size' => (int) $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
        ];
    }

    public function delete(?string $path, ?string $disk = null): void
    {
        if (! $path || $this->isUnsafePath($path)) {
            return;
        }

        $this->disk($disk)->delete($path);
    }

    public function exists(string $path, ?string $disk = null): bool
    {
        return ! $this->isUnsafePath($path) && $this->disk($disk)->exists($path);
    }

    public function response(
        string $path,
        string $downloadName,
        string $mime,
        bool $inline = false,
        ?string $disk = null,
    ): StreamedResponse {
        abort_if($this->isUnsafePath($path) || ! $this->exists($path, $disk), 404);

        $safeName = str_replace(['"', "\r", "\n"], '', $downloadName);
        $disposition = $inline ? 'inline' : 'attachment';

        return $this->disk($disk)->response($path, $safeName, [
            'Content-Type' => $mime !== '' ? $mime : 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function assertSafe(UploadedFile $file, string $kind): void
    {
        if (! isset(self::KINDS[$kind])) {
            throw ValidationException::withMessages(['file' => 'نوع المرفق غير مدعوم.']);
        }

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => $this->uploadErrorMessage($file->getError()),
            ]);
        }

        $extension = $this->extension($file);

        if (in_array($extension, self::DANGEROUS_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'file' => 'نوع الملف غير مسموح لأسباب أمنية.',
            ]);
        }

        $config = self::KINDS[$kind];

        if (! in_array($extension, $config['extensions'], true)) {
            throw ValidationException::withMessages([
                'file' => 'نوع الملف غير مدعوم. الأنواع المسموحة: '.implode(', ', $config['extensions']).'.',
            ]);
        }

        $sizeKb = (int) ceil(((int) $file->getSize()) / 1024);

        if ($sizeKb > $config['max_kb']) {
            throw ValidationException::withMessages([
                'file' => 'حجم الملف أكبر من الحد المسموح ('.$this->humanMax($config['max_kb']).').',
            ]);
        }

        $mime = $this->detectedMime($file);

        if ($mime === 'application/octet-stream') {
            return;
        }

        if (! in_array($mime, $config['mimes'], true)) {
            throw ValidationException::withMessages([
                'file' => 'نوع الملف غير مدعوم.',
            ]);
        }
    }

    public function humanMax(int $kilobytes): string
    {
        if ($kilobytes >= 1024) {
            return round($kilobytes / 1024, 1).' ميغابايت';
        }

        return $kilobytes.' كيلوبايت';
    }

    private function disk(?string $name): Filesystem
    {
        return Storage::disk($name ?: self::defaultDisk());
    }

    private function extension(UploadedFile $file): string
    {
        return strtolower($file->getClientOriginalExtension() ?: (string) $file->guessExtension());
    }

    private function detectedMime(UploadedFile $file): string
    {
        return strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream'));
    }

    private function originalName(UploadedFile $file): string
    {
        $name = str_replace(["\0", '/', '\\'], '', $file->getClientOriginalName());

        return basename($name) ?: 'file';
    }

    private function isUnsafePath(string $path): bool
    {
        return $path === ''
            || str_contains($path, '..')
            || str_starts_with($path, '/')
            || str_contains($path, ':');
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم الملف أكبر من الحد المسموح.',
            UPLOAD_ERR_PARTIAL => 'لم يكتمل رفع الملف. حاول مرة أخرى.',
            UPLOAD_ERR_NO_FILE => 'لم يُرفق أي ملف.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'حدث خطأ في التخزين.',
            default => 'فشل رفع الملف.',
        };
    }
}
