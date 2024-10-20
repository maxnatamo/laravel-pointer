<?php

namespace Pointer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Pointer\Tour;

class TourFailed
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Tour $tour
    ) {}
}
