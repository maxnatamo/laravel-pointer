<?php

use Pointer\Tour;
use Pointer\TourStep;
use Pointer\Traits\HasOwner;

describe('Tour::makeUnowned', function () {
    it('can create new tours')
        ->expect(fn() => Tour::makeUnowned('some-tour'))
        ->throwsNoExceptions();

    it('can creates new tours in database')
        ->defer(fn() => Tour::makeUnowned('some-tour'))
        ->assertDatabaseHas('tours', ['name' => 'some-tour', 'owner_id' => null]);

    it('throws InvalidArgumentException given empty name')
        ->expect(fn() => Tour::makeUnowned(''))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given non-tour type')
        ->expect(fn() => Tour::makeUnowned(new class {}))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given owned tour type')
        ->expect(fn() => Tour::makeUnowned(new class extends Tour
        {
            use HasOwner;
        }))
        ->throws(\InvalidArgumentException::class);

    it('creates tour given tour type with string steps')
        ->expect(fn() => Tour::makeUnowned(new class extends Tour
        {
            public string $name = 'test-tour';

            protected array $steps = ['step-1', 'step-2', 'step-3'];
        }))
        ->name->toBe('test-tour')
        ->steps()->toHaveCount(3)
        ->sequence(
            fn($step) => $step->name->toBe('step-1'),
            fn($step) => $step->name->toBe('step-2'),
            fn($step) => $step->name->toBe('step-3'),
        )
        ->each->toBeInstanceOf(TourStep::class);
});
