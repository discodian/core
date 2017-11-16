<?php

namespace Discodian\Core\Providers;

use Discodian\Core\Listeners;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventProvider extends ServiceProvider
{
    protected $listeners = [
        Listeners\MessageNormalizer::class,
        Listeners\HelloHandler::class,
        Listeners\DispatchHandler::class
    ];

    public function register()
    {
        /** @var Dispatcher $events */
        $events = $this->app->make('events');

        foreach ($this->listeners as $listener) {
            $events->subscribe($listener);
        }
    }
}
