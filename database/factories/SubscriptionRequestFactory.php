<?php

namespace Database\Factories;

use App\Enums\SubscriptionRequestStatus;
use App\Models\Course;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionRequest>
 */
class SubscriptionRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::factory();

        return [
            'student_id' => User::factory()->student(),
            'course_id' => $course,
            'package_id' => SubscriptionPackage::factory()->state(fn (array $attributes) => [
                'course_id' => $attributes['course_id'] ?? $course,
            ]),
            'receipt_image_path' => 'receipts/example.jpg',
            'status' => SubscriptionRequestStatus::Pending,
            'rejection_reason' => null,
            'reviewed_at' => null,
        ];
    }
}
