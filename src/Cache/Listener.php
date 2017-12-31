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

namespace Discodian\Core\Cache;

use Discodian\Core\Events\Parts\Cached;
use Discodian\Core\Events\Parts\Delete;
use Discodian\Core\Events\Parts\Get;
use Discodian\Core\Events\Parts\Set;
use Discodian\Parts\Part;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;

class Listener
{
    /**
     * @var Repository
     */
    private $cache;
    /**
     * @var Dispatcher
     */
    private $events;

    public function __construct(Repository $cache, Dispatcher $events)
    {
        $this->cache = $cache;
        $this->events = $events;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Get::class, [$this, 'get']);
        $events->listen(Set::class, [$this, 'cache']);
        $events->listen(Delete::class, [$this, 'forget']);
    }

    public function get(Get $event)
    {
        $key = $this->key($event->class, $event->id);

        if ($key && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        logs("Cache miss for $key.");
    }

    public function cache(Set $event)
    {
        $key = $this->keyFromPart($event->part);

        if ($key) {
            $this->cache->forever($key, $event->part);

            $this->events->dispatch(new Cached($event->part));
        }
    }

    public function forget(Delete $event)
    {
        $key = $this->keyFromPart($event->part);

        if ($key) {
            $this->cache->forget($key);
        }
    }

    protected function keyFromPart(Part $part): ?string
    {
        return $this->key(get_class($part), $part->getKey());
    }

    protected function key(string $class, string $id = null): ?string
    {
        if (! $id) {
            return false;
        }

        return sprintf('parts.%s.%d', $class, $id);
    }
}
