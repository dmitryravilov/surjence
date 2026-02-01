<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Sentiment;
use App\Models\Headline;
use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeadlineService
{
    public function __construct(private readonly string $goServiceUrl) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAndProcessHeadlines(): array
    {
        $cacheKey = 'headlines:daily:'.now()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addDay(), function (): array {
            try {
                $response = Http::timeout(10)->get("{$this->goServiceUrl}/api/v1/headlines/raw");

                if (! $response->successful()) {
                    Log::error('Failed to fetch headlines from Go service', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return $this->getCachedOrEmpty();
                }

                /** @var array<string, mixed> $data */
                $data = $response->json();
                /** @var array<int, array<string, mixed>> $rawHeadlines */
                $rawHeadlines = $data['headlines'] ?? (is_array($data) ? $data : []);

                return $this->processHeadlines($rawHeadlines);
            } catch (\Exception $e) {
                Log::error('Error fetching headlines', ['error' => $e->getMessage()]);

                return $this->getCachedOrEmpty();
            }
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawHeadlines
     * @return array<int, array<string, mixed>>
     */
    private function processHeadlines(array $rawHeadlines): array
    {
        $processed = [];

        foreach ($rawHeadlines as $raw) {
            /** @var array<string, mixed> $raw */
            $hash = isset($raw['hash']) && is_string($raw['hash']) ? $raw['hash'] : '';
            $title = isset($raw['title']) && is_string($raw['title']) ? $raw['title'] : '';
            $source = isset($raw['source']) && is_string($raw['source']) ? $raw['source'] : '';
            $url = isset($raw['url']) && is_string($raw['url']) ? $raw['url'] : '';
            $description = isset($raw['description']) && is_string($raw['description']) ? $raw['description'] : null;
            $publishedAt = $raw['publishedAt'] ?? now();
            $sentimentRaw = isset($raw['sentiment']) && is_string($raw['sentiment']) ? $raw['sentiment'] : 'neutral';
            $sentimentScore = isset($raw['sentimentScore']) && is_numeric($raw['sentimentScore'])
                ? (float) $raw['sentimentScore']
                : 0.0;
            $keywords = isset($raw['keywords']) && is_array($raw['keywords']) ? $raw['keywords'] : [];

            $headline = Headline::firstOrCreate(
                ['hash' => $hash],
                [
                    'title' => $title,
                    'source' => $source,
                    'url' => $url,
                    'description' => $description,
                    'published_at' => $publishedAt,
                    'sentiment' => Sentiment::fromString($sentimentRaw)->value,
                    'sentiment_score' => $sentimentScore,
                    'keywords' => $keywords,
                ]
            );

            if (! $headline->theme_id) {
                $theme = $this->assignTheme($headline);
                $headline->update(['theme_id' => $theme->id]);
            }

            if (! $headline->reflection) {
                $headline->update(['reflection' => $this->generateReflection($headline)]);
            }

            $processed[] = $this->formatHeadline($headline);
        }

        return $processed;
    }

    /**
     * Always returns a Theme instance.
     */
    private function assignTheme(Headline $headline): Theme
    {
        $keywords = $headline->keywords ?? [];
        $themes = Theme::all();

        foreach ($themes as $theme) {
            $themeKeywords = $this->getThemeKeywords($theme->name);
            foreach ($keywords as $keyword) {
                if (is_string($keyword) && in_array(strtolower($keyword), $themeKeywords, true)) {
                    return $theme;
                }
            }
        }

        return Theme::firstOrCreate(
            ['name' => 'General'],
            ['description' => 'General news', 'color' => '#6366f1']
        );
    }

    /**
     * @return array<int, string>
     */
    private function getThemeKeywords(string $themeName): array
    {
        $mapping = [
            'Technology' => ['tech', 'technology', 'digital', 'software', 'ai', 'artificial', 'computer'],
            'Politics' => ['politics', 'political', 'government', 'election', 'policy', 'senate', 'congress'],
            'Business' => ['business', 'economy', 'market', 'trade', 'financial', 'stock', 'company'],
            'Health' => ['health', 'medical', 'doctor', 'hospital', 'disease', 'treatment', 'medicine'],
            'Science' => ['science', 'research', 'study', 'scientific', 'discovery', 'experiment'],
            'Environment' => ['climate', 'environment', 'green', 'carbon', 'renewable', 'energy', 'sustainability'],
            'Mindfulness' => [
                'mindfulness', 'meditation', 'mental health', 'wellness', 'mindful', 'meditate', 'mental',
                'wellbeing', 'well-being', 'self-care', 'awareness', 'presence', 'calm', 'peace', 'zen',
                'yoga', 'therapy', 'counseling', 'psychology',
            ],
        ];

        return $mapping[$themeName] ?? [];
    }

    private function generateReflection(Headline $headline): string
    {
        $reflections = [
            Sentiment::Positive->value => [
                'A moment of progress in our shared journey.',
                'A reminder that positive change is possible.',
                'Something to appreciate in today\'s news.',
            ],
            Sentiment::Negative->value => [
                'A complex situation that deserves thoughtful consideration.',
                'A challenge that calls for understanding and care.',
                'An opportunity to reflect on how we respond to difficulty.',
            ],
            Sentiment::Neutral->value => [
                'An update worth noting, without urgency.',
                'Information to consider at your own pace.',
                'A piece of the larger picture, calmly presented.',
            ],
        ];

        $sentiment = Sentiment::fromString(is_string($headline->sentiment) ? $headline->sentiment : 'neutral');
        $options = $reflections[$sentiment->value];

        return $options[array_rand($options)];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatHeadline(Headline $headline): array
    {
        return [
            'id' => $headline->id,
            'title' => $headline->title,
            'source' => $headline->source,
            'url' => $headline->url,
            'description' => $headline->description,
            'published_at' => $headline->published_at?->toIso8601String(),
            'sentiment' => $headline->sentiment,
            'keywords' => $headline->keywords ?? [],
            'theme' => $headline->theme !== null ? [
                'id' => $headline->theme->id,
                'name' => $headline->theme->name,
                'color' => $headline->theme->color,
            ] : null,
            'reflection' => $headline->reflection,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCachedOrEmpty(): array
    {
        $yesterdayKey = 'headlines:daily:'.now()->subDay()->format('Y-m-d');

        /** @var array<int, array<string, mixed>> $cached */
        $cached = Cache::get($yesterdayKey, []);

        return $cached;
    }
}
