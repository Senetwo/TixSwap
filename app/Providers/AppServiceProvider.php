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
        $this->app->bind(\App\Services\ProofCheck\ProofCheckService::class, function ($app) {
            $config = $app['config']['services.ai_proof'];

            $reader = match ($config['driver'] ?? 'none') {
                'claude' => new \App\Services\ProofCheck\ClaudeProofReader(
                    new \Anthropic\Client(apiKey: $config['api_key'] ?: null),
                    $config['model'],
                ),
                'fake' => new \App\Services\ProofCheck\FakeProofReader,
                default => null,
            };

            return new \App\Services\ProofCheck\ProofCheckService($reader);
        });

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
