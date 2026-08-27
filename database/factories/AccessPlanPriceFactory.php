<?php

namespace Database\Factories;

use App\Models\AccessPlan;
use App\Models\AccessPlanPrice;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessPlanPrice>
 */
class AccessPlanPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'access_plan_id' => AccessPlan::factory(),
            'region_id' => Region::query()->where('code', 'gaza')->value('id') ?? Region::factory(),
            'price' => 100,
            'sale_price' => null,
            'currency' => 'ILS',
        ];
    }
}
