<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Target>
 */
class TargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Target::class;
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'gol' => $this->faker->randomElement(['A', 'B', 'C']),
            'targetAmount' => 5000000,
            'currentAmount' => 1000000,
            'startDate' => Carbon::now()->toDateString(),
            'endDate' => Carbon::now()->addMonths(6)->toDateString(),
            'file' => 'dummy.pdf',
        ];
    }
}
