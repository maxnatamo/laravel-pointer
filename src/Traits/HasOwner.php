<?php

namespace Pointer\Traits;

use Pointer\Traits\Tourable;

trait HasOwner
{
    /**
     * Get the owner of the tour.
     *
     * @return Tourable
     */
    public final function owner(): Tourable
    {
        return $this->tour->owner;
    }
}
