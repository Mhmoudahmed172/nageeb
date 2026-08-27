<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\AccessPlan;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessPlan>
 */
class AccessPlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'المادة كاملة',
            'description' => null,
            'status' => ContentStatus::Live,
            'access_duration_days' => null,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
