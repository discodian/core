<?php

namespace Discodian\Core\Requests;

use Discodian\Core\Events\Parts\Get;
use Discodian\Core\Factory\Factory;
use Illuminate\Contracts\Events\Dispatcher;

class Listener
{
    /**
     * @var Resource
     */
    protected $resource;

    public function __construct(Resource $resource, Factory $factory)
    {
        $this->resource = $resource;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Get::class, [$this, 'request']);
    }

    public function request(Get $event)
    {
        return $this->resource
            ->setPart($event->class)
            ->get($event->id);
    }
}
