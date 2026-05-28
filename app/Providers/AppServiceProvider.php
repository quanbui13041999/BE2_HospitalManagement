<?php

namespace App\Providers;

use App\Services\Doctor\DayOffService;
use App\Services\Doctor\RecurringScheduleService;
use App\Models\HealthTracking;
use App\Policies\HealthTrackingPolicy;
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

        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        \Illuminate\Pagination\Paginator::useBootstrapFive();

        Gate::policy(HealthTracking::class, HealthTrackingPolicy::class);

        \App\Models\QueueTicket::observe(\App\Observers\QueueTicketObserver::class);
    }
}
