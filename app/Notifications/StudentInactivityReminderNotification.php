<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentInactivityReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Course $course, public User $teacher) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'تذكير من معلّمك',
            'body' => $this->teacher->name.' يدعوك لمتابعة مادة «'.$this->course->title.'».',
            'url' => route('student.my-courses.show', $this->course),
        ];
    }
}
