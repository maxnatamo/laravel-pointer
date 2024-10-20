<?php

declare(strict_types=1);

namespace Pointer;

enum TourStatus: string
{
    case Created = 'created';
    case Started = 'started';
    case Restarted = 'restarted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
