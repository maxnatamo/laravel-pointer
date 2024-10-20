<?php

namespace Pointer\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Pointer\Tour;

trait Tourable
{
    /**
     * A model may have multiple tour.
     */
    public function tour(string $name): ?Tour
    {
        return $this->tours->where('name', $name)->first();
    }

    /**
     * A model may have multiple tours.
     */
    public function tours(): MorphMany
    {
        return $this->morphMany(Tour::class, 'owner');
    }
}
