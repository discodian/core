<?php

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Heartbeat;
use Discodian\Core\Response\Factory;
use Discodian\Extend\Responses\Response;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use React\Promise\Deferred;

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
    /**
     * @var Factory
     */
    private $response;

    public function __construct(Schedule $schedule, Application $app, Factory $response)
    {
        $this->schedule = $schedule;
        $this->app = $app;
        $this->response = $response;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Heartbeat::class, [$this, 'run']);
    }

    public function run(Heartbeat $event)
    {
        $defer = new Deferred();

        $due = $this->schedule->dueEvents($this->app);
        $completed = null;
        /** @var Event $event */
        foreach ($due as $event) {
            if (! $event->filtersPass($this->app)) {
                continue;
            }

            if (!($event instanceof CallbackEvent)) {
                throw new \InvalidArgumentException("Only callback events allowed.");
            }

            logs("running event: ". $event->getSummaryForDisplay());
            $response = $event->run($this->app);

            if (is_array($response)) {
                $response = Arr::first($response, function ($item) {
                    return $item instanceof Response;
                });
            }

            if ($response instanceof Response) {
                $this->response->respond($response);
            }
        }

        $defer->resolve([$due, $completed]);
    }
}
