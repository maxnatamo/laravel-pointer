<?php

use Illuminate\Support\Facades\Config;
use Pointer\Models\StoredTour;
use Pointer\Tour;
use Pointer\TourStatus;

describe('Tour pruning', function () {
    it('throws InvalidArgumentException given invalid prune mode', function () {
        Config::set('pointer.prune.mode', 'invalid');

        $tour = Tour::makeUnowned('tour')
            ->addSteps(['step-1', 'step-2', 'step-3'])
            ->finish(now()->subMonth());

        expect(fn() => $tour->stored()->pruneAll())->toThrow(InvalidArgumentException::class);
    });

    it('prunes successfully (shallow)', function () {
        Config::set('pointer.prune.mode', 'shallow');

        $tours = collect([
            Tour::makeUnowned('tour-1')->addSteps(['step-1', 'step-2', 'step-3']),
            Tour::makeUnowned('tour-2')->addSteps(['step-1', 'step-2', 'step-3'])->finish(now()->subMonth()),
            Tour::makeUnowned('tour-3')->addSteps(['step-1', 'step-2', 'step-3']),
            Tour::makeUnowned('tour-4')->addSteps(['step-1', 'step-2', 'step-3'])->finish(now()->subMonth()),
        ]);

        $tours->each(fn($tour) => $tour->stored()->pruneAll());

        expect(StoredTour::all())
            ->toHaveCount(4)
            ->sequence(
                fn($tour) => $tour
                    ->name->toBe('tour-1')
                    ->steps()->get()->toHaveCount(3),
                fn($tour) => $tour
                    ->name->toBe('tour-2')
                    ->steps()->get()->toBeEmpty(),
                fn($tour) => $tour
                    ->name->toBe('tour-3')
                    ->steps()->get()->toHaveCount(3),
                fn($tour) => $tour
                    ->name->toBe('tour-4')
                    ->steps()->get()->toBeEmpty(),
            );
    });

    it('prunes successfully (full)', function () {
        Config::set('pointer.prune.mode', 'full');

        $tours = collect([
            Tour::makeUnowned('tour-1')->addSteps(['step-1', 'step-2', 'step-3']),
            Tour::makeUnowned('tour-2')->addSteps(['step-1', 'step-2', 'step-3'])->finish(now()->subMonth()),
            Tour::makeUnowned('tour-3')->addSteps(['step-1', 'step-2', 'step-3']),
            Tour::makeUnowned('tour-4')->addSteps(['step-1', 'step-2', 'step-3'])->finish(now()->subMonth()),
        ]);

        $tours->each(fn($tour) => $tour->stored()->pruneAll());

        expect(StoredTour::all())
            ->toHaveCount(2)
            ->sequence(
                fn($tour) => $tour
                    ->name->toBe('tour-1')
                    ->steps()->get()->toHaveCount(3),
                fn($tour) => $tour
                    ->name->toBe('tour-3')
                    ->steps()->get()->toHaveCount(3),
            );
    });

    it('only prunes tours of minimum age', function () {
        Config::set('pointer.prune.mode', 'full');
        Config::set('pointer.prune.min_age', '2 weeks');

        $tours = collect([
            Tour::makeUnowned('tour-1')->addStep('step')->finish(now()->subWeek()),
            Tour::makeUnowned('tour-2')->addStep('step')->finish(now()->subMonth()),
            Tour::makeUnowned('tour-3')->addStep('step')->finish(now()->subWeek()),
            Tour::makeUnowned('tour-4')->addStep('step')->finish(now()->subWeek()),
        ]);

        $tours->each(fn($tour) => $tour->stored()->pruneAll());

        expect(StoredTour::all())
            ->toHaveCount(3)
            ->sequence(
                fn($tour) => $tour->name->toBe('tour-1'),
                fn($tour) => $tour->name->toBe('tour-3'),
                fn($tour) => $tour->name->toBe('tour-4'),
            );
    });

    it('prunes all completed tours with `min_age` = `null`', function () {
        Config::set('pointer.prune.mode', 'full');
        Config::set('pointer.prune.min_age', null);

        $tours = collect([
            Tour::makeUnowned('tour-1')->addStep('step')->finish(now()->subWeek()),
            Tour::makeUnowned('tour-2')->addStep('step')->finish(now()->subMonth()),
            Tour::makeUnowned('tour-3')->addStep('step')->finish(now()->subWeek()),
            Tour::makeUnowned('tour-4')->addStep('step')->finish(now()->subWeek()),
        ]);

        $tours->each(fn($tour) => $tour->stored()->pruneAll());

        expect(StoredTour::all())->toBeEmpty();
    });
});
