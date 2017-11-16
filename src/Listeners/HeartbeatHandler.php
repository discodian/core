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

use Discodian\Core\Events\Ws\Heartbeat;
use Discodian\Core\Events\Ws\HeartbeatAcknowledge;
use Discodian\Core\Socket\Op;
use Illuminate\Contracts\Events\Dispatcher;

class HeartbeatHandler
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Heartbeat::class, [$this, 'heartbeat']);
        $events->listen(HeartbeatAcknowledge::class, [$this, 'acknowledge']);
    }

    public function heartbeat(Heartbeat $event)
    {
        $sequence = $event->data->d;

        logs("Heartbeat received, sequence {$sequence}");

        $event->send([
            'op' => Op::HEARTBEAT,
            'd' => $sequence
        ]);
    }

    public function acknowledge(HeartbeatAcknowledge $event)
    {
        $heartbeat = $event->connector()->heartbeat();

        $received = microtime(true);
        $diff = $received - $heartbeat->last();
        $time = $diff * 1000;

        $heartbeat->cancelAcknowledgeTimer();

        logs("Received heartbeat acknowledge, response in $time");
    }
}
