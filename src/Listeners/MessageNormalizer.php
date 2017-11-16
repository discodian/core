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
use Discodian\Core\Socket\Op;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

class MessageNormalizer
{
    /**
     * @var Dispatcher
     */
    protected $events;
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Dispatcher $events, Application $app)
    {
        $this->events = $events;
        $this->app = $app;
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
            Connector::sequence($data->s);
        }

        logs("Raw message decoded", (array) $data);

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
            logs($data->op);
            $proxy = $codes[$data->op];
            logs($data->op);
            $this->events->dispatch(new $proxy($data));
            logs($data->op);
        } else {
            logs("No action taken for op {$data->op}.");
        }
    }
}
