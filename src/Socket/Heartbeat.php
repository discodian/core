<?php

namespace Discodian\Core\Socket;

use Amp\Loop;

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
     * @var string
     */
    protected $timer;

    /**
     * The Loop interval reference for the acknowledge timer.
     * @var string
     */
    protected $acknowledgeTimer;

    /**
     * The time the last heartbeat package was send.
     *
     * @var int
     */
    protected $last;

    public function beat()
    {
        Connector::send([
            'op' => EventCode::HEARTBEAT,
            'd' => Connector::sequence()
        ]);

        $this->last = microtime(true);

        $this->acknowledgeTimer = Loop::delay(
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
            Loop::cancel($this->timer);
        }

        $this->timer = Loop::repeat($interval, [$this, 'beat']);
        $this->beat();
    }

    public function cancel()
    {
        if ($this->timer) {
            Loop::cancel($this->timer);
        }
        if ($this->acknowledgeTimer) {
            Loop::cancel($this->acknowledgeTimer);
        }
    }
}
