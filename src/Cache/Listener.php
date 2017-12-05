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
use Discodian\Core\Events\Parts\Deleted;
use Discodian\Core\Events\Parts\Loaded;
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
        $events->listen(Loaded::class, [$this, 'cache']);
        $events->listen(Deleted::class, [$this, 'forget']);
    }

    protected function key(Part $part): string
    {
        return sprintf('parts.%s.%d', get_class($part), $part->id);
    }

    public function cache(Loaded $event)
    {
        $this->cache->forever($this->key($event->part), $event->part);

        $this->events->dispatch(new Cached($event->part));
    }

    public function forget(Deleted $event)
    {
        $this->cache->forget($this->key($event->part));
    }
}
