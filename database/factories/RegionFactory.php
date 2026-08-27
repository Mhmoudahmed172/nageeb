<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'code' => fake()->unique()->slug(2),
            'status' => ContentStatus::Live,
            'position' => 1,
        ];
    }
}
