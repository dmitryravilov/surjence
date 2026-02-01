<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['name' => 'Technology', 'description' => 'Tech news and innovations', 'color' => '#3b82f6'],
            ['name' => 'Politics', 'description' => 'Political developments', 'color' => '#ef4444'],
            ['name' => 'Business', 'description' => 'Business and economy', 'color' => '#10b981'],
            ['name' => 'Health', 'description' => 'Health and wellness', 'color' => '#f59e0b'],
            ['name' => 'Science', 'description' => 'Scientific discoveries', 'color' => '#8b5cf6'],
            ['name' => 'Environment', 'description' => 'Climate and environment', 'color' => '#06b6d4'],
            ['name' => 'Mindfulness', 'description' => 'Mindfulness, meditation, and mental wellness', 'color' => '#A78BFA'],
            ['name' => 'General', 'description' => 'General news', 'color' => '#6366f1'],
        ];

        foreach ($themes as $theme) {
            Theme::firstOrCreate(
                ['name' => $theme['name']],
                $theme
            );
        }
    }
}
