<?php

use Illuminate\Support\Facades\Event;
use Pointer\Events\TourCancelled;
use Pointer\Events\TourCompleted;
use Pointer\Events\TourCreated;
use Pointer\Events\TourFailed;
use Pointer\Events\TourStarted;
use Pointer\Tour;

describe('Tour events', function () {
    it('dispatches TourCreated on creation', function () {
        Event::fake([TourCreated::class]);

        Tour::make('tour', $this->user);

        Event::assertDispatched(fn(TourCreated $event) => $event->tour->name === 'tour');
    });

    it('dispatches TourCreated on unowned creation', function () {
        Event::fake([TourCreated::class]);

        Tour::makeUnowned('tour');

        Event::assertDispatched(fn(TourCreated $event) => $event->tour->name === 'tour');
    });

    it('dispatches TourStarted on start', function () {
        Event::fake([TourStarted::class]);

        Tour::makeUnowned('tour')->start();

        Event::assertDispatched(
            fn(TourStarted $event) => $event->tour->name === 'tour' && !$event->restarted
        );
    });

    it('dispatches TourStarted on restart', function () {
        Event::fake([TourStarted::class]);

        Tour::makeUnowned('tour')->restart();

        Event::assertDispatched(
            fn(TourStarted $event) => $event->tour->name === 'tour' && $event->restarted
        );
    });

    it('dispatches TourFailed on fail', function () {
        Event::fake([TourFailed::class]);

        Tour::makeUnowned('tour')->fail();

        Event::assertDispatched(fn(TourFailed $event) => $event->tour->name === 'tour');
    });

    it('dispatches TourCompleted on finish', function () {
        Event::fake([TourCompleted::class]);

        Tour::makeUnowned('tour')->finish();

        Event::assertDispatched(fn(TourCompleted $event) => $event->tour->name === 'tour');
    });

    it('dispatches TourCancelled on cancellation', function () {
        Event::fake([TourCancelled::class]);

        Tour::makeUnowned('tour')->cancel();

        Event::assertDispatched(fn(TourCancelled $event) => $event->tour->name === 'tour');
    });
});
