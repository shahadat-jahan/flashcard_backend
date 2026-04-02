<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence;

        return [
            'author_id' => $this->faker->numberBetween(1, User::all()->count()),
            'task_id' => $this->faker->numberBetween(1, Task::all()->count()),
            'title' => $title,
            'slug' => Str::slug($title),
            'type' => $this->faker->numberBetween(1, 3),
            'content' => $this->faker->paragraphs(3, true),
            'status' => $this->faker->randomElement([0, 1]),
            'created_by' => $this->faker->numberBetween(1, 5),
        ];
    }
}
