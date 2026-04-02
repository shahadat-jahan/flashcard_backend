<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Task::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'assign_user_id' => $this->faker->numberBetween(1, User::all()->count()),
            'subject' => $this->faker->sentence(),
            'note' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
