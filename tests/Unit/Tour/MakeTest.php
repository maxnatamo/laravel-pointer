<?php

use Pointer\Tour;
use Pointer\TourStep;
use Pointer\Traits\HasOwner;

describe('Tour::make', function () {
    it('can create new tours')
        ->expect(fn() => Tour::make('some-tour', $this->user))
        ->throwsNoExceptions();

    it('can creates new tours in database')
        ->defer(fn() => Tour::make('some-tour', $this->user))
        ->assertDatabaseHas('tours', ['name' => 'some-tour', 'owner_id' => 1]);

    it('throws InvalidArgumentException given empty name')
        ->expect(fn() => Tour::make('', $this->user))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given non-tour type')
        ->expect(fn() => Tour::make(new class {}, $this->user))
        ->throws(\InvalidArgumentException::class);

    it('throws InvalidArgumentException given unowned tour type')
        ->expect(fn() => Tour::make(new class extends Tour {}, $this->user))
        ->throws(\InvalidArgumentException::class);

    it('creates tour given owned tour type')
        ->expect(fn() => Tour::make(new class extends Tour
        {
            use HasOwner;

            public string $name = 'test-tour';
        }, $this->user))
        ->throwsNoExceptions();

    it('throws InvalidArgumentException given user without Tourable-trait')
        ->expect(fn() => Tour::make('some-tour', $this->userWithoutTourable))
        ->throws(\InvalidArgumentException::class);

    it('creates tour given tour type with string steps')
        ->expect(fn() => Tour::make(new class extends Tour
        {
            use HasOwner;

            public string $name = 'test-tour';

            protected array $steps = ['step-1', 'step-2', 'step-3'];
        }, $this->user))
        ->name->toBe('test-tour')
        ->steps()->toHaveCount(3)
        ->sequence(
            fn($step) => $step->name->toBe('step-1'),
            fn($step) => $step->name->toBe('step-2'),
            fn($step) => $step->name->toBe('step-3'),
        )
        ->each->toBeInstanceOf(TourStep::class);
});
