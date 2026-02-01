<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ThemeFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Headline> $headlines
 * @property-read int|null $headlines_count
 *
 * @method static ThemeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Theme newModelQuery()
 * @method static Builder<static>|Theme newQuery()
 * @method static Builder<static>|Theme query()
 * @method static Builder<static>|Theme whereColor($value)
 * @method static Builder<static>|Theme whereCreatedAt($value)
 * @method static Builder<static>|Theme whereDescription($value)
 * @method static Builder<static>|Theme whereId($value)
 * @method static Builder<static>|Theme whereName($value)
 * @method static Builder<static>|Theme whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Theme extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function headlines(): HasMany
    {
        return $this->hasMany(Headline::class);
    }
}
