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

namespace Discodian\Core\Socket;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

final class Heartbeat
{
    /**
     * The heartbeat interval.
     *
     * @var int
     */
    protected $interval;

    /**
     * The Loop interval reference.
     *
     * @var TimerInterface
     */
    protected $timer;

    /**
     * The Loop interval reference for the acknowledge timer.
     * @var TimerInterface
     */
    protected $acknowledgeTimer;

    /**
     * The time the last heartbeat package was send.
     *
     * @var int
     */
    protected $last;
    /**
     * @var LoopInterface
     */
    protected $loop;
    /**
     * @var Connector
     */
    protected $connector;

    public function __construct(LoopInterface $loop, Connector $connector)
    {
        $this->loop = $loop;
        $this->connector = $connector;
    }

    public function beat()
    {
        $this->connector->send([
            'op' => Op::HEARTBEAT,
            'd' => $this->connector->sequence()
        ]);

        $this->last = microtime(true);

        $this->acknowledgeTimer = $this->loop->addTimer(
            $this->interval / 1000,
            function () {
                if (! $this->connector->connected()) {
                    return;
                }

                $this->connector->ws()->close(Op::CLOSE_HEARTBEAT_ACK_MISSING, 'no ack heartbeat received');
            }
        );
    }

    public function setup(int $interval)
    {
        $this->interval = $interval;

        if ($this->timer) {
            $this->timer->cancel();
            $this->timer = null;
        }

        $this->timer = $this->loop->addPeriodicTimer($interval, [$this, 'beat']);
        $this->beat();
    }

    public function cancel()
    {
        if ($this->timer) {
            $this->timer->cancel();
            $this->timer = null;
        }
        $this->cancelAcknowledgeTimer();
    }

    public function cancelAcknowledgeTimer()
    {
        if ($this->acknowledgeTimer) {
            $this->acknowledgeTimer->cancel();
            $this->acknowledgeTimer = null;
        }
    }

    public function last()
    {
        return $this->last;
    }
}
