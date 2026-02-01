<?php

namespace Tests\Feature;

use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeadlineApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_headlines_endpoint_returns_success(): void
    {
        Theme::factory()->create(['name' => 'General']);

        $response = $this->getJson('/api/v1/headlines');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'count',
                'meta' => ['fetched_at'],
            ]);
    }

    public function test_headlines_endpoint_returns_array(): void
    {
        Theme::factory()->create(['name' => 'General']);

        $response = $this->getJson('/api/v1/headlines');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_themes_endpoint_returns_success(): void
    {
        Theme::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/themes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'count',
            ]);
    }

    public function test_themes_endpoint_returns_themes(): void
    {
        $theme = Theme::factory()->create(['name' => 'Technology']);

        $response = $this->getJson('/api/v1/themes');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('count'));
    }
}
