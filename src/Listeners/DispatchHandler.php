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

        if (method_exists($this, $readableEvent)) {
            $this->{$readableEvent}($event);
        }
    }

    public function ready(Dispath $event)
    {
    }
}
