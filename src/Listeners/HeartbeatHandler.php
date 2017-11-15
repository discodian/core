<?php

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Heartbeat;
use Discodian\Core\Events\Ws\HeartbeatAcknowledge;
use Discodian\Core\Socket\EventCode;
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
        $event->send([
            'op' => EventCode::HEARTBEAT,
            'd' => $event->data->d
        ]);
    }

    public function acknowledge(HeartbeatAcknowledge $event)
    {
        $received = microtime(true);
        $diff = $received - static::$heartbeat;
        $time = $diff * 1000;

        static::$timer->cancel();

    }
}
