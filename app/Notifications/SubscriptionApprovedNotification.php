<?php

namespace App\Notifications;

use App\Models\SubscriptionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public SubscriptionRequest $subscriptionRequest) {}

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
        $course = $this->subscriptionRequest->course;

        return [
            'title' => 'تمت الموافقة على طلب الاشتراك',
            'body' => 'يمكنك الآن الوصول إلى مادة «'.$course->title.'».',
            'url' => route('student.my-courses.show', $course),
        ];
    }
}
