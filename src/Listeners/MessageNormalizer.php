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
use Discodian\Core\Socket\Op;
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

    public function normalize(Events\Message $event)
    {
        $message = $event->message;
        $data = $message->getPayload();

        if ($message->isBinary()) {
            $data = zlib_decode($data);
        }

        $data = json_decode($data);

        if (isset($data->s)) {
            $event->connector()->sequence($data->s);
        }

        logs(
            "Raw message decoded (size: {$message->getPayloadLength()})",
            $message->getPayloadLength() < 500 ?
                (array)$data :
                []
        );

        $this->events->dispatch('ws.raw', $data);

        $codes = [
            Op::DISPATCH => Events\Dispatch::class,
            Op::RECONNECT => Events\Reconnect::class,
            Op::INVALID_SESSION => Events\InvalidSession::class,
            Op::HELLO => Events\Hello::class,
            Op::HEARTBEAT => Events\Heartbeat::class,
            Op::HEARTBEAT_ACK => Events\HeartbeatAcknowledge::class
        ];

        if (array_key_exists($data->op, $codes)) {
            $class = $codes[$data->op];
            $this->events->dispatch(new $class($data));
        } else {
            logs("No action taken for op {$data->op}.");
        }
    }
}
