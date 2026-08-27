<?php

namespace Database\Factories;

use App\Enums\SubscriptionRequestStatus;
use App\Models\AccessPlan;
use App\Models\Course;
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
        return [
            'student_id' => User::factory()->student(),
            'course_id' => Course::factory(),
            'package_id' => null,
            'access_plan_id' => null,
            'receipt_image_path' => 'receipts/example.jpg',
            'status' => SubscriptionRequestStatus::Pending,
            'rejection_reason' => null,
            'reviewed_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (SubscriptionRequest $request): void {
            if ($request->access_plan_id || ! $request->course_id) {
                return;
            }

            $request->access_plan_id = AccessPlan::factory()->create([
                'course_id' => $request->course_id,
            ])->id;
        });
    }
}
