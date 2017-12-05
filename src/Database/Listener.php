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
