<?php

namespace Discodian\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Discodian\Parts\Contracts\Registry as Contract;
use Discodian\Parts\Registry;

class PartProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            Contract::class,
            Registry::class
        );
    }
}
