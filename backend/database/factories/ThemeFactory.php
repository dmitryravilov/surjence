<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Technology', 'Politics', 'Business', 'Health', 'Science', 'Environment', 'General']),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->hexColor(),
        ];
    }
}
