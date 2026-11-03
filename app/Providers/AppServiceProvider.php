<?php

namespace App\Providers;

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
        //
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
