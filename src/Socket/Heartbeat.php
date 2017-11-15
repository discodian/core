<?php

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

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function beat()
    {
        Connector::send([
            'op' => EventCode::HEARTBEAT,
            'd' => Connector::sequence()
        ]);

        $this->last = microtime(true);

        $this->acknowledgeTimer = $this->loop->addTimer(
            $this->interval / 1000,
            function() {
                if (! Connector::connected()) {
                    return;
                }

                Connector::ws()->close(1001, 'no ack heartbeat received');
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
        if ($this->acknowledgeTimer) {
            $this->acknowledgeTimer->cancel();
            $this->acknowledgeTimer = null;
        }
    }
}
