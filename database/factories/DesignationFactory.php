<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topic>
 */
class DesignationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namesArray = [
            'Software Engineer', 'Dev Ops', 'UI/UX', 'QA', 'Web Developer', 'Web Designer', 'Graphic Designer',
            'Network Engineer',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($namesArray),
        ];
    }
}
