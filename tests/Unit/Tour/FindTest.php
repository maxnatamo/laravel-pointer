<?php

use Pointer\Tour;

describe('Tour::find', function () {
    it('can find unowned tours with ID')
        ->defer(fn() => Tour::makeUnowned('tour'))
        ->expect(fn() => Tour::find(1))
        ->name->toBe('tour');

    it('can find unowned tours with name')
        ->defer(fn() => Tour::makeUnowned('tour'))
        ->expect(fn() => Tour::find('tour'))
        ->name->toBe('tour');

    it('can find owned tours with ID and matching owner')
        ->defer(fn() => Tour::make('tour', $this->user))
        ->expect(fn() => Tour::find(1, $this->user))
        ->name->toBe('tour');

    it('can find owned tours with name and matching owner')
        ->defer(fn() => Tour::make('tour', $this->user))
        ->expect(fn() => Tour::find('tour', $this->user))
        ->name->toBe('tour');

    it('cannot find owned tours with ID but different owner')
        ->defer(fn() => Tour::make('tour', $this->user))
        ->expect(fn() => Tour::find(1, $this->otherUser))
        ->toBeNull();

    it('cannot find owned tours with name but different owner')
        ->defer(fn() => Tour::make('tour', $this->user))
        ->expect(fn() => Tour::find('tour', $this->otherUser))
        ->toBeNull();

    it('throws InvalidArgumentException given owner without Tourable trait')
        ->expect(fn() => Tour::find('tour', $this->userWithoutTourable))
        ->throws(\InvalidArgumentException::class);
});
