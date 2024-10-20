<?php

namespace Pointer\Listeners;

use Illuminate\Events\Dispatcher;
use Pointer\Events\TourCancelled;
use Pointer\Events\TourCompleted;
use Pointer\Events\TourCreated;
use Pointer\Events\TourFailed;
use Pointer\Events\TourStarted;
use Pointer\Events\TourStatusUpdated;
use Pointer\Events\TourStepFinished;
use Pointer\Events\TourStepUpdated;
use Pointer\Events\TourStepStarted;
use Pointer\TourStatus;

class TourEventSubscriber
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(TourStatusUpdated::class, function (TourStatusUpdated $event): void {
            $event->tour->setStatus($event->after);

            match ($event->after) {
                TourStatus::Created => TourCreated::dispatch($event->tour),
                TourStatus::Started => TourStarted::dispatch($event->tour),
                TourStatus::Restarted => TourStarted::dispatch($event->tour, true),
                TourStatus::Failed => TourFailed::dispatch($event->tour),
                TourStatus::Completed => TourCompleted::dispatch($event->tour),
                TourStatus::Cancelled => TourCancelled::dispatch($event->tour),
            };
        });

        $events->listen(TourStepUpdated::class, function (TourStepUpdated $event): void {
            TourStepFinished::dispatchIf($event->before != null, $event->tour, $event->before);
            TourStepStarted::dispatchIf($event->after != null, $event->tour, $event->after);
        });
    }
}
