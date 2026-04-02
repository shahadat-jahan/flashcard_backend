<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namesArray = [
            'PHP', 'HTML', 'CSS', 'JavaScript', 'SQL',
            'React', 'Vue.js', 'Angular', 'Node.js', 'Express.js',
            'Django', 'Ruby', 'Python', 'Java', 'Spring Boot',
            'Swift', 'Kotlin', 'Flutter', 'Xamarin', 'Svelte',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($namesArray),
        ];
    }
}
