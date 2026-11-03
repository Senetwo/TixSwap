<?php

namespace App\Providers;

use App\Services\Mpesa\DarajaGateway;
use App\Services\Mpesa\FakeGateway;
use App\Services\Mpesa\MpesaGateway;
use App\View\Components\TixSwapLayout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MpesaGateway::class, function ($app) {
            $config = $app['config']['services.mpesa'];

            if (($config['driver'] ?? 'fake') === 'daraja') {
                return new DarajaGateway($config);
            }

            // The fake driver approves every payment, so it must never run live.
            if ($app->environment('production')) {
                throw new \RuntimeException('MPESA_DRIVER=fake is not allowed in production. Set MPESA_DRIVER=daraja.');
            }

            return new FakeGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Views use <x-tixswap-layout>, but Laravel's automatic naming for the
        // TixSwapLayout class is <x-tix-swap-layout>. Register the short name
        // so both tags work.
        Blade::component('tixswap-layout', TixSwapLayout::class);
    }
}
