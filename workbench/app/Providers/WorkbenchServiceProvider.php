<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Pointer\Listeners\TourEventSubscriber;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::subscribe(TourEventSubscriber::class);
    }
}
