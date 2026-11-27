<?php

namespace App\Providers;

use App\Models\RecapItem;
use App\Models\RecapPeriod;
use App\Policies\RecapItemPolicy;
use App\Policies\RecapPeriodPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(RecapPeriod::class, RecapPeriodPolicy::class);
        Gate::policy(RecapItem::class, RecapItemPolicy::class);
    }
}
