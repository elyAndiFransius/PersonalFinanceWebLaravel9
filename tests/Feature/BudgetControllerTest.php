<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Budget;

class BudgetControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
     use RefreshDatabase;

    public function test_user_can_get_budget_data()
    {
        $user = User::factory()->create();
        Budget::factory()->create(['user_id' => $user->id]); // hanya satu budget

        $this->actingAs($user)
            ->getJson('/api/budgets')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'pemasukkan', 'priode', 'categories']
                ]
            ]);
    }
}
