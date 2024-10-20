<?php

namespace Pointer\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Event;
use Pointer\Listeners\TourEventSubscriber;

class TourServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/pointer.php' => config_path('pointer.php'),
        ]);

        $this->publishesMigrations([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ]);

        Event::subscribe(TourEventSubscriber::class);

        $this->registerAbout();
    }

    protected function registerAbout(): void
    {
        if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
            return;
        }

        AboutCommand::add('Pointer', static fn() => [
            'Version' => InstalledVersions::getPrettyVersion('maxnatamo/laravel-pointer'),
        ]);
    }
}
