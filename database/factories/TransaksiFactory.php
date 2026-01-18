<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),

            // pastikan category ada
            'categories_id' => Category::factory(),

            'jenis' => $this->faker->randomElement(['pemasukkan', 'pengeluaran']),
            'descripsi' => $this->faker->sentence(4),
            'jumlah' => $this->faker->numberBetween(10000, 1000000),
            'date' => $this->faker->date(),
        ];
    }
}
