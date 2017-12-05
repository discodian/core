<?php

namespace Discodian\Core\Database;

use Discodian\Core\Events\Parts\Loaded;
use Illuminate\Contracts\Events\Dispatcher;

class Listener
{
    public function subscribe(Dispatcher $events)
    {
        if (config('database.default')) {
            $events->listen(Loaded::class, [$this, 'persist']);
        }
    }

    public function persist(Loaded $event)
    {
        Resource::forPart($event->part)->save();
    }
}
