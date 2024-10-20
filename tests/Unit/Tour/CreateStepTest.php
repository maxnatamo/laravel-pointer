<?php

use Pointer\Tour;
use Pointer\TourStep;

describe('Tour::createStep', function () {
    it('returns the same name')
        ->expect(fn() => Tour::makeUnowned('some-tour')->createStep('step-1'))
        ->name->toBe('step-1')
        ->tour()->name->toBe('some-tour');

    it('returns the same name in the class')
        ->expect(fn() => Tour::makeUnowned('some-tour')->createStep(new class extends TourStep
        {
            public string $name = 'step-1';
        }))
        ->name->toBe('step-1')
        ->tour()->name->toBe('some-tour');
});
