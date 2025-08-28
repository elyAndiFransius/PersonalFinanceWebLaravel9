<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Category;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // relasi otomatis ke user
            'pemasukkan' => $this->faker->numberBetween(100000, 1000000),
            'priode' => $this->faker->randomElement(['Harian', 'Mingguan', 'Bulanan']),

            ];
        }
            // ⬇ Tambahkan fungsi configure di bawah definition()
    public function configure()
    {
        return $this->afterCreating(function ($budget) {
            Category::factory()->count(3)->create([
                'budget_id' => $budget->id,
            ]);
        });
    }
}
