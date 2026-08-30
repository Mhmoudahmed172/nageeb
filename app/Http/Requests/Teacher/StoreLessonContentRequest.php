<?php

namespace App\Http\Requests\Teacher;

use App\Enums\LessonContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLessonContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isTeacher();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(LessonContentType::class)],
            'url' => ['nullable', 'string', 'max:2048'],
            'file' => ['nullable', 'file'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = LessonContentType::tryFrom((string) $this->input('type'));

            if ($type === null) {
                return;
            }

            $file = $this->file('file');

            if ($this->isMediaType($type) && $file === null) {
                $validator->errors()->add('file', 'يجب اختيار ملف للرفع.');

                return;
            }

            if (! $this->isMediaType($type) && $file !== null) {
                $validator->errors()->add('file', 'هذا النوع من المحتوى لا يقبل رفع ملفات.');
            }
        });
    }

    public function contentType(): LessonContentType
    {
        return LessonContentType::from((string) $this->validated('type'));
    }

    public function mediaKind(): ?string
    {
        return match ($this->contentType()) {
            LessonContentType::Video => MediaStore::KIND_VIDEO,
            LessonContentType::Audio => MediaStore::KIND_AUDIO,
            LessonContentType::File => MediaStore::KIND_FILE,
            default => null,
        };
    }

    public function isMediaType(?LessonContentType $type = null): bool
    {
        $type ??= $this->contentType();

        return in_array($type, [
            LessonContentType::Video,
            LessonContentType::Audio,
            LessonContentType::File,
        ], true);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'نوع المحتوى',
            'file' => 'الملف',
            'url' => 'الرابط',
        ];
    }
}
