<?php

namespace Database\Factories;

use App\Models\BudgetGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetGoal>
 */
class BudgetGoalFactory extends Factory
{
    protected $model = BudgetGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['budget', 'goal']),
            'category_id' => null,
            'period_type' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'target_amount' => $this->faker->numberBetween(100000, 10000000),
            'target_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'last_notified_progress' => null,
        ];
    }

    /**
     * Indicate that the budget goal is a budget.
     */
    public function budget(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'budget',
        ]);
    }

    /**
     * Indicate that the budget goal is a goal.
     */
    public function goal(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'goal',
        ]);
    }
}

