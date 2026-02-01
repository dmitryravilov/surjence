<?php

declare(strict_types=1);

namespace App\Enums;

enum Sentiment: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Neutral = 'neutral';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'positive' => self::Positive,
            'negative' => self::Negative,
            default => self::Neutral,
        };
    }
}
