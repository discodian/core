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
        $data = $event->data->d;

        logs("Hello received, heartbeat interval at {$data->heartbeat_interval}.", $data->_trace);

        $connector = $event->connector();

        if (!$connector->identify()) {
            $connector->heartbeat()->setup($data->heartbeat_interval);
        }
    }
}
