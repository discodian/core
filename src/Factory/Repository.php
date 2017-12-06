<?php

namespace Discodian\Core\Factory;

use Discodian\Core\Events\Parts as Events;
use Discodian\Parts;
use Discodian\Parts\Part;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Repository
{
    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var array
     */
    protected $map = [
        Parts\Guild\Guild::class => []
    ];

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function get(string $class, string $id): ?Part
    {
        $part = $this->events->until(new Events\Get($class, $id));

        if (! $part) {
            logs("Failed to load $class::$id");
        }

        return $part;
    }

    public function set(Part $part)
    {
        if (Arr::has($this->map, get_class($part))) {
            $this->map[get_class($part)][$part->getKey()] = $part->getKey();
        }

        $this->events->dispatch(new Events\Set($part));
    }

    public function delete(Part $part)
    {
        if (Arr::has($this->map, get_class($part))) {
            Arr::forget($this->map, sprintf('%s.%s', get_class($part), $part->getKey()));
        }

        $this->events->dispatch(new Events\Delete($part));
    }

    public function all(string $class): Collection
    {
        $collect = new Collection();

        if (Arr::has($this->map, $class)) {
            foreach (Arr::get($this->map, $class) as $id) {
                $collect->put($id, $this->get($class, $id));
            }
        }

        return $collect;
    }

    public function deleteIds(string $class, array $ids)
    {
        foreach ($ids as $id) {
            $part = $this->get($class, $id);

            if ($part) {
                $this->delete($part);
            }
        }
    }
}
