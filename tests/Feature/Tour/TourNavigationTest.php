<?php

use Pointer\Tour;
use Pointer\TourStatus;

describe('Tour navigation', function () {
    it('can navigate an entire tour', function () {
        $tour = Tour::makeUnowned('tour')->addSteps([
            'step-1',
            'step-2',
            'step-3',
        ]);

        expect($tour->current())->toBeNull();

        $tour->start();
        expect($tour->current()->name)->toBe('step-1');

        $tour->next();
        expect($tour->current()->name)->toBe('step-2');

        $tour->next();
        expect($tour->current()->name)->toBe('step-3');

        $tour->next();
        expect($tour->current())->toBeNull();
        expect($tour->status())->toBe(TourStatus::Completed);
    });

    it('can navigate back and forth', function () {
        $tour = Tour::makeUnowned('tour')->addSteps([
            'step-1',
            'step-2',
            'step-3',
        ]);

        expect($tour->current())->toBeNull();

        $tour->start();
        expect($tour->current()->name)->toBe('step-1');

        $tour->next();
        expect($tour->current()->name)->toBe('step-2');

        $tour->previous();
        expect($tour->current()->name)->toBe('step-1');

        $tour->previous();
        expect($tour->current()->name)->toBe('step-1');
    });

    it('can restart', function () {
        $tour = Tour::makeUnowned('tour')->addSteps([
            'step-1',
            'step-2',
            'step-3',
        ]);

        $tour->start();
        $tour->next();
        $tour->restart();

        expect($tour->current()->name)->toBe('step-1');
    });

    it('cannot be navigated when not mutable', function () {
        $tour = Tour::makeUnowned('tour')->addSteps([
            'step-1',
            'step-2',
            'step-3',
        ]);

        $tour->next();
        $tour->next();
        $tour->fail();

        expect(fn() => $tour->next())->toThrow(Exception::class);
        expect(fn() => $tour->previous())->toThrow(Exception::class);
    });
});
