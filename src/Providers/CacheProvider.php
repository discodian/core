<?php

namespace Discodian\Core\Providers;

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Support\ServiceProvider;

class CacheProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(CacheServiceProvider::class);
    }
}
