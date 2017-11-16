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

use Discodian\Core\Events\Ws as Events;
use Discodian\Core\Socket\Connector;
use Discodian\Core\Socket\EventCode;
use Illuminate\Contracts\Events\Dispatcher;

class MessageNormalizer
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
        $events->listen(Events\Message::class, [$this, 'normalize']);
    }

    public function normalize(Events\Message $message)
    {
        $data = $message->getPayload();

        if ($message->isBinary()) {
            $data = zlib_decode($data);
        }

        $data = json_decode($data);

        if (isset($data->s)) {
            Connector::sequence($data->s);
        }

        $this->events->dispatch('ws.raw', $data);

        $codes = [
            EventCode::DISPATCH => Events\Dispatch::class,
            EventCode::RECONNECT => Events\Reconnect::class,
            EventCode::INVALID_SESSION => Events\InvalidSession::class,
            EventCode::HELLO => Events\Hello::class,
            EventCode::HEARTBEAT => Events\Heartbeat::class,
            EventCode::HEARTBEAT_ACK => Events\HeartbeatAcknowledge::class
        ];

        if (isset($codes[$data->op])) {
            $this->events->dispatch(call_user_func([$codes[$data->op], '__construct'], $data));
        }
    }
}
