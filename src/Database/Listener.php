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

namespace Discodian\Core\Database;

use Discodian\Core\Events\Parts\Deleted;
use Discodian\Core\Events\Parts\Loaded;
use Discodian\Core\Events\Parts\Persisted;
use Discodian\Parts\Part;
use Illuminate\Contracts\Events\Dispatcher;

class Listener
{
    /**
     * @var Dispatcher
     */
    private $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Loaded::class, [$this, 'persist']);
        $events->listen(Deleted::class, [$this, 'delete']);
    }

    public function persist(Loaded $event)
    {
        $this->model($event->part)->save();

        $this->events->dispatch(new Persisted($event->part));
    }

    public function delete(Deleted $event)
    {
        $this->model($event->part)->delete();
    }

    protected function model(Part $part): Resource
    {
        return Resource::forPart($part);
    }
}
