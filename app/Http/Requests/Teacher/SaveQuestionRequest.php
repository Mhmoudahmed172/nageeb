<?php

namespace App\Http\Requests\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\Question;
use App\Models\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->isTeacher()) {
            return false;
        }

        // Ownership is checked before validation so a foreign question id never
        // leaks through a validation error message.
        $question = $this->route('question');

        return ! $question instanceof Question || $question->isOwnedBy($user->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teacherId = $this->user()->id;
        $ownedCourseIds = Course::query()->where('teacher_id', $teacherId)->select('id');

        return [
            'type' => ['required', Rule::enum(QuestionType::class)],
            'text' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['required', 'numeric', 'min:0.25', 'max:100'],
            'difficulty' => ['required', Rule::enum(QuestionDifficulty::class)],
            'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')->where('teacher_id', $teacherId)],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->whereIn(
                    'semester_id',
                    Semester::query()->whereIn('course_id', $ownedCourseIds)->select('id'),
                ),
            ],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*.text' => ['required', 'string', 'max:1000'],
            'correct_options' => ['required', 'array', 'min:1'],
            'correct_options.*' => ['integer', Rule::in(array_keys((array) $this->input('options', [])))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = QuestionType::tryFrom((string) $this->input('type'));
            $correct = (array) $this->input('correct_options', []);

            if ($type && ! $type->allowsMultipleCorrectOptions() && count($correct) > 1) {
                $validator->errors()->add('correct_options', 'هذا النوع من الأسئلة يقبل إجابة صحيحة واحدة فقط.');
            }

            if ($type === QuestionType::TrueFalse && count((array) $this->input('options', [])) !== 2) {
                $validator->errors()->add('options', 'سؤال صح/خطأ يجب أن يحتوي على خيارين فقط.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function questionAttributes(): array
    {
        return $this->safe()->only([
            'type',
            'text',
            'explanation',
            'points',
            'difficulty',
            'course_id',
            'unit_id',
        ]);
    }

    /**
     * @return array<int, array{text: string, is_correct: bool, position: int}>
     */
    public function optionRows(): array
    {
        $correct = array_map('intval', (array) $this->validated('correct_options'));

        return collect($this->validated('options'))
            ->values()
            ->map(fn (array $option, int $index) => [
                'text' => $option['text'],
                'is_correct' => in_array($index, $correct, true),
                'position' => $index + 1,
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'نوع السؤال',
            'text' => 'نص السؤال',
            'points' => 'الدرجة',
            'difficulty' => 'مستوى الصعوبة',
            'course_id' => 'المادة',
            'unit_id' => 'الوحدة',
            'options' => 'الخيارات',
            'correct_options' => 'الإجابة الصحيحة',
        ];
    }
}
