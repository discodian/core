<?php

namespace Discodian\Core\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class SchedulingProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Schedule::class);
    }
}
