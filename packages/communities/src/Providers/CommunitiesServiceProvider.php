<?php

namespace Loja\Communities\Providers;

use Illuminate\Support\ServiceProvider;

class CommunitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
