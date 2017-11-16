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

use Discodian\Core\Socket\Heartbeat as Beat;
use Discodian\Core\Events\Ws\Heartbeat;
use Discodian\Core\Events\Ws\HeartbeatAcknowledge;
use Discodian\Core\Socket\EventCode;
use Illuminate\Contracts\Events\Dispatcher;

class HeartbeatHandler
{
    /**
     * @var Beat
     */
    protected $beat;

    public function __construct(Heartbeat $beat)
    {
        $this->beat = $beat;
    }

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
    }
}
