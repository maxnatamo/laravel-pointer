<?php

use Pointer\Tour;
use Pointer\TourStep;

describe('TourStep::make', function () {
    it('can create new steps')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), 'step-1'))
        ->throwsNoExceptions();

    it('can creates new tours in database')
        ->defer(fn() => TourStep::make(Tour::makeUnowned('tour'), 'step-1'))
        ->assertDatabaseHas('tour_steps', ['name' => 'step-1']);

    it('throws InvalidArgumentException given empty name')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), ''))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given null name')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), null))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given non-TourStep class')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), new class {}))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given unset name in class')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), new class extends TourStep {}))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given empty name in class')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), new class extends TourStep
        {
            public string $name = '';
        }))
        ->throws(\InvalidArgumentException::class);

    it('creates step with owner tour')
        ->expect(fn() => TourStep::make(Tour::makeUnowned('tour'), 'step-1'))
        ->tour()->name->toBe('tour');
});
