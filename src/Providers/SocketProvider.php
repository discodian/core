<?php

namespace Discodian\Core\Providers;

use Illuminate\Support\ServiceProvider;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class SocketProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoopInterface::class, function ($app) {
            return Factory::create();
        });
    }
}
