<?php

namespace Database\Factories;

use App\Enums\PayoutRequestStatus;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayoutRequest>
 */
class PayoutRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'amount' => 100,
            'bank_details' => 'بنك فلسطين — 123456',
            'status' => PayoutRequestStatus::Pending,
        ];
    }
}
