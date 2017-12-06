<?php

namespace Discodian\Core\Factory;

use Discodian\Core\Events\Parts as Events;
use Discodian\Parts\Part;
use Illuminate\Contracts\Events\Dispatcher;

class Repository
{
    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var array
     */
    protected $map;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
        $this->map = [];
    }

    public function get(string $class, string $id): ?Part
    {
        $part = $this->events->until(new Events\Get($class, $id));

        if (! $part) {
            logs("Failed to load $class::$id");
        }

        return $part;
    }

    public function delete(Part $part)
    {
        $this->events->dispatch(new Events\Delete($part));
    }

    public function loaded(Part &$part)
    {
        $this->events->dispatch(new Events\Loaded($part));
    }
}
