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

namespace Discodian\Core\Providers;

use Discodian\Core\Listeners;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventProvider extends ServiceProvider
{
    protected $listeners = [
        Listeners\DispatchHandler::class,
        Listeners\HeartbeatHandler::class,
        Listeners\HelloHandler::class,
        Listeners\MessageNormalizer::class,
        Listeners\ReadyHandler::class,
        Listeners\SchedulingHandler::class,
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
