<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HeadlineFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $hash
 * @property string $title
 * @property string $source
 * @property string $url
 * @property string|null $description
 * @property Carbon|null $published_at
 * @property string $sentiment
 * @property float $sentiment_score
 * @property array<array-key, mixed>|null $keywords
 * @property int|null $theme_id
 * @property string|null $reflection
 * @property bool $is_active
 * @property Carbon|null $displayed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Theme|null $theme
 *
 * @method static HeadlineFactory factory($count = null, $state = [])
 * @method static Builder<static>|Headline newModelQuery()
 * @method static Builder<static>|Headline newQuery()
 * @method static Builder<static>|Headline query()
 * @method static Builder<static>|Headline whereCreatedAt($value)
 * @method static Builder<static>|Headline whereDescription($value)
 * @method static Builder<static>|Headline whereDisplayedAt($value)
 * @method static Builder<static>|Headline whereHash($value)
 * @method static Builder<static>|Headline whereId($value)
 * @method static Builder<static>|Headline whereIsActive($value)
 * @method static Builder<static>|Headline whereKeywords($value)
 * @method static Builder<static>|Headline wherePublishedAt($value)
 * @method static Builder<static>|Headline whereReflection($value)
 * @method static Builder<static>|Headline whereSentiment($value)
 * @method static Builder<static>|Headline whereSentimentScore($value)
 * @method static Builder<static>|Headline whereSource($value)
 * @method static Builder<static>|Headline whereThemeId($value)
 * @method static Builder<static>|Headline whereTitle($value)
 * @method static Builder<static>|Headline whereUpdatedAt($value)
 * @method static Builder<static>|Headline whereUrl($value)
 *
 * @mixin Eloquent
 */
class Headline extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'hash',
        'title',
        'source',
        'url',
        'description',
        'published_at',
        'sentiment',
        'sentiment_score',
        'keywords',
        'theme_id',
        'reflection',
        'is_active',
        'displayed_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Theme, Headline>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
