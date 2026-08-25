<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPackage>
 */
class SubscriptionPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => 'باقة الفصل الأول',
            'price_gaza' => 50,
            'price_west_bank_abroad' => 80,
            'duration_label' => 'فصل دراسي',
        ];
    }
}
