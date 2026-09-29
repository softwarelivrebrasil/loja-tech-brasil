<?php

namespace Loja\Withdrawals\Providers;

use Loja\Withdrawals\Adapters\LocalWithdrawalGatewayAdapter;
use Loja\Withdrawals\Contracts\WithdrawalGatewayContract;
use Illuminate\Support\ServiceProvider;

class WithdrawalsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WithdrawalGatewayContract::class, LocalWithdrawalGatewayAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
