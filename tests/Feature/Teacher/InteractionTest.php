<?php

namespace Tests\Feature\Teacher;

use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\CommentRepliedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_reply_and_notifies_student(): void
    {
        Notification::fake();

        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $unit = Unit::factory()->forCourse($course)->create();
        $lesson = Lesson::factory()->create(['unit_id' => $unit->id]);
        $question = Comment::factory()->create([
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'parent_id' => null,
            'message' => 'سؤال الطالب',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.interactions.index'))
            ->assertOk()
            ->assertSee('سؤال الطالب')
            ->assertSee($student->name);

        $this->actingAs($teacher)
            ->post(route('teacher.interactions.reply', $question), [
                'message' => 'هذا هو الرد',
            ])
            ->assertRedirect(route('teacher.interactions.index'));

        $this->assertDatabaseHas('comments', [
            'parent_id' => $question->id,
            'user_id' => $teacher->id,
            'message' => 'هذا هو الرد',
            'lesson_id' => $lesson->id,
        ]);

        Notification::assertSentTo($student, CommentRepliedNotification::class);
    }
}
