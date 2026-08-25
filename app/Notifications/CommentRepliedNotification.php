<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(public Comment $reply) {}

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
        $lesson = $this->reply->lesson;

        return [
            'title' => 'رد جديد على سؤالك',
            'body' => 'رد المعلّم على سؤالك في درس «'.$lesson->title.'».',
            'url' => route('student.my-courses.show', [
                'course' => $lesson->unit->course_id,
                'lesson' => $lesson->id,
            ]),
        ];
    }
}
