<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreCourseRequest;
use App\Http\Requests\Teacher\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->forTeacher(auth()->id())
            ->latest()
            ->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('teacher.courses.create', ['course' => null]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::query()->create([
            ...$request->courseAttributes(),
            'teacher_id' => auth()->id(),
            'cover_image' => $this->storeCover($request->file('cover_image')),
        ]);

        $this->storeAttachments($course, $request->file('attachments', []));

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم إنشاء المادة. يمكنك الآن إدارة الوحدات والدروس.');
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);
        $course->load('attachments');

        return view('teacher.courses.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $payload = $request->courseAttributes();

        if ($request->hasFile('cover_image')) {
            if ($course->cover_image) {
                Storage::disk('public')->delete($course->cover_image);
            }
            $payload['cover_image'] = $this->storeCover($request->file('cover_image'));
        }

        $course->update($payload);
        $this->storeAttachments($course, $request->file('attachments', []));

        $destination = $request->input('save_action') === 'continue'
            ? route('teacher.courses.content', $course)
            : route('teacher.courses.edit', $course);

        return redirect()
            ->to($destination)
            ->with('status', 'تم حفظ تعديلات المادة.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_if($course->enrollments()->exists(), 422);
        abort_if($course->subscriptionRequests()->exists(), 422);

        $course->delete();

        return redirect()
            ->route('teacher.courses.index')
            ->with('status', 'تم حذف المادة.');
    }

    private function storeCover(?UploadedFile $file): ?string
    {
        return $file?->store('courses/covers', 'public');
    }

    /**
     * @param  array<int, UploadedFile>|null  $files
     */
    private function storeAttachments(Course $course, ?array $files): void
    {
        if (! $files) {
            return;
        }

        $position = (int) $course->attachments()->max('position');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $position++;
            CourseAttachment::query()->create([
                'course_id' => $course->id,
                'name' => $file->getClientOriginalName(),
                'path' => $file->store('courses/attachments', 'public'),
                'position' => $position,
            ]);
        }
    }
}
