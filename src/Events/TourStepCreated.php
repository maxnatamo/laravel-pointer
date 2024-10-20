<?php

namespace Pointer\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Pointer\Tour;
use Pointer\TourStep;

class TourStepCreated
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Tour $tour,
        public readonly TourStep $step,
    ) {}
}
