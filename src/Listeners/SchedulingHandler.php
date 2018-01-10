<?php

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Heartbeat;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

class SchedulingHandler
{
    /**
     * @var Schedule
     */
    private $schedule;
    /**
     * @var Application
     */
    private $app;

    public function __construct(Schedule $schedule, Application $app)
    {
        $this->schedule = $schedule;
        $this->app = $app;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Heartbeat::class, [$this, 'run']);
    }

    public function run(Heartbeat $event)
    {
        /** @var Event $event */
        foreach ($this->schedule->dueEvents($this->app) as $event) {
            if (! $event->filtersPass($this->app)) {
                continue;
            }

            logs("running event: ". $event->getSummaryForDisplay());
            $event->run($this->app);
        }
    }
}
