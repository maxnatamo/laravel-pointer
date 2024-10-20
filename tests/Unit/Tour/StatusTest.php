<?php

use Pointer\Tour;
use Pointer\TourStatus;

describe('Tour::status', function () {
    it('defaults to TourStatus::Created')
        ->expect(fn() => Tour::makeUnowned('some-tour')->status())
        ->toBe(TourStatus::Created);

    it('becomes TourStatus::Started when starting')
        ->expect(fn() => Tour::makeUnowned('some-tour')->start()->status())
        ->toBe(TourStatus::Started);

    it('becomes TourStatus::Started when re-starting')
        ->expect(fn() => Tour::makeUnowned('some-tour')->restart()->status())
        ->toBe(TourStatus::Started);

    it('becomes TourStatus::Cancelled when cancelling')
        ->expect(fn() => Tour::makeUnowned('some-tour')->cancel()->status())
        ->toBe(TourStatus::Cancelled);

    it('becomes TourStatus::Completed when finishing')
        ->expect(fn() => Tour::makeUnowned('some-tour')->finish()->status())
        ->toBe(TourStatus::Completed);

    it('becomes TourStatus::Failed when failing')
        ->expect(fn() => Tour::makeUnowned('some-tour')->fail()->status())
        ->toBe(TourStatus::Failed);
});
