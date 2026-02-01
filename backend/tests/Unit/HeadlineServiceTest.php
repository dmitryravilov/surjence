<?php

namespace Tests\Unit;

use App\Models\Theme;
use App\Services\HeadlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HeadlineServiceTest extends TestCase
{
    use RefreshDatabase;

    private HeadlineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HeadlineService(goServiceUrl: 'http://go-service:8080');
    }

    public function test_service_caches_headlines(): void
    {
        Theme::factory()->create(['name' => 'General']);

        $mockResponse = [
            'headlines' => [
                [
                    'hash' => 'test-hash-1',
                    'title' => 'Test Headline',
                    'source' => 'Test Source',
                    'url' => 'https://example.com',
                    'description' => 'Test description',
                    'publishedAt' => now()->toIso8601String(),
                    'sentiment' => 'neutral',
                    'sentimentScore' => 0,
                    'keywords' => ['test'],
                ],
            ],
        ];

        Http::fake([
            'go-service:8080/api/v1/headlines/raw' => Http::response($mockResponse, 200),
        ]);

        Cache::flush();

        $result1 = $this->service->fetchAndProcessHeadlines();
        $result2 = $this->service->fetchAndProcessHeadlines();

        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);

        // Second call should use cache, so only one HTTP request
        Http::assertSentCount(1);
    }

    public function test_service_creates_headline_in_database(): void
    {
        Theme::factory()->create(['name' => 'General']);

        $mockResponse = [
            'headlines' => [
                [
                    'hash' => 'unique-hash-123',
                    'title' => 'New Headline',
                    'source' => 'Test Source',
                    'url' => 'https://example.com/article',
                    'description' => 'Article description',
                    'publishedAt' => now()->toIso8601String(),
                    'sentiment' => 'positive',
                    'sentimentScore' => 0.5,
                    'keywords' => ['news', 'test'],
                ],
            ],
        ];

        Http::fake([
            'go-service:8080/api/v1/headlines/raw' => Http::response($mockResponse, 200),
        ]);

        Cache::flush();

        $this->service->fetchAndProcessHeadlines();

        $this->assertDatabaseHas('headlines', [
            'hash' => 'unique-hash-123',
            'title' => 'New Headline',
        ]);
    }
}
