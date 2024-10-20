<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;
use Orchestra\Testbench\Attributes\WithMigration;
use Pointer\Providers\TourServiceProvider;
use Workbench\App\Models\UserWithoutTourable;

use function Orchestra\Testbench\workbench_path;

#[WithMigration]
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public User $user;

    public User $otherUser;

    public UserWithoutTourable $userWithoutTourable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $this->otherUser = User::create([
            'name' => 'Other Test User',
            'email' => 'other@example.com',
            'password' => Hash::make('password')
        ]);

        $this->userWithoutTourable = UserWithoutTourable::create([
            'name' => 'Untourable Test User',
            'email' => 'untour@example.com',
            'password' => Hash::make('password')
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom([
            workbench_path('database/migrations'),
            workbench_path('../database/migrations'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('pointer.table_names.tours', 'tours');
        $app['config']->set('pointer.table_names.tour_steps', 'tour_steps');
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TourServiceProvider::class,
        ];
    }
}
