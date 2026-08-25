<?php

namespace App\Notifications;

use App\Models\SubscriptionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionRejectedNotification extends Notification
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
        $reason = $this->subscriptionRequest->rejection_reason;

        return [
            'title' => 'تم رفض طلب الاشتراك',
            'body' => $reason
                ? 'رُفض طلبك لمادة «'.$this->subscriptionRequest->course->title.'»: '.$reason
                : 'رُفض طلبك لمادة «'.$this->subscriptionRequest->course->title.'».',
            'url' => route('courses.subscribe', $this->subscriptionRequest->course),
        ];
    }
}
