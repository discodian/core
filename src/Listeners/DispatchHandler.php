<?php

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Dispatch;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class DispatchHandler
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Dispatch::class, [$this, 'dispatch']);
    }

    public function dispatch(Dispatch $event)
    {

        $readableEvent = Str::camel($event->data->t);
        logs("Received dispatch event {$readableEvent}", (array) $event);

        if (method_exists($this, $readableEvent))
        {
            $this->{$readableEvent}($event);
        }
    }

    public function ready(Dispath $event)
    {

    }
}
