<?php

/*
 * This file is part of the Discodian bot toolkit.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://discodian.com
 * @see https://github.com/discodian
 */

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Dispatch;
use Discodian\Core\Events\Ws\Ready;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use React\Promise\Deferred;

class DispatchHandler
{
    /**
     * @var Dispatcher
     */
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Dispatch::class, [$this, 'dispatch']);
    }

    public function dispatch(Dispatch $event)
    {
        $readableEvent = Str::camel(Str::lower($event->data->t));
        $eventClass = "Discodian\\Core\\Socket\\Events\\" . Str::studly(Str::lower($event->data->t));

        if (method_exists($this, $readableEvent)) {
            logs("Dispatching event to local override {$readableEvent}");
            $this->{$readableEvent}($event);
        } elseif (class_exists($eventClass)) {
            logs("Dispatching event to event class {$eventClass}");
            $this->dispatchEvent($eventClass, $event->data);
        } else {
            logs("No dispatch found for {$event->data->t}: $eventClass or $readableEvent");
        }
    }

    public function dispatchEvent(string $event, $data)
    {
        $defer = new Deferred();

        $event = app($event);

        $event($defer, $data);
    }

    public function ready(Dispatch $event)
    {
        $this->events->dispatch(new Ready($event->data));
    }
}
