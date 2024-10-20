<?php

use Pointer\Tour;
use Workbench\App\Models\User;

describe('Tour context', function () {
    it('returns null on new tours', function () {
        expect(Tour::makeUnowned('tour')->context())->toBeNull();
    });

    it('allows context to be retrieved', function () {
        Tour::makeUnowned('tour')->setContext(['state' => 1]);

        expect(Tour::find('tour')->context())->toMatchArray(['state' => 1]);
    });

    it('allows context to be cleared', function () {
        $tour = Tour::makeUnowned('tour');

        $tour->setContext(['state' => 1]);
        expect(Tour::find('tour')->context())->not->toBeNull();

        $tour->clearContext();
        expect(Tour::find('tour')->context())->toBeNull();
    });
});
