<?php

namespace Database\Factories;

use App\Models\Headline;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeadlineFactory extends Factory
{
    protected $model = Headline::class;

    public function definition(): array
    {
        return [
            'hash' => $this->faker->sha256(),
            'title' => $this->faker->sentence(),
            'source' => $this->faker->company(),
            'url' => $this->faker->url(),
            'description' => $this->faker->paragraph(),
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'sentiment' => $this->faker->randomElement(['positive', 'negative', 'neutral']),
            'sentiment_score' => $this->faker->randomFloat(2, -1, 1),
            'keywords' => $this->faker->words(5),
            'theme_id' => Theme::factory(),
            'reflection' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
