<?php

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Hello;
use Illuminate\Contracts\Events\Dispatcher;

class HelloHandler
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Hello::class, [$this, 'hello']);
    }

    public function hello(Hello $event)
    {
        $event->log()->debug("Hello received.");
    }
}
