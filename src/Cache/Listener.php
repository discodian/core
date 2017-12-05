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

use Discodian\Core\Events\Parts\Loaded;
use Illuminate\Contracts\Events\Dispatcher;

class Listener
{
    public function subscribe(Dispatcher $events)
    {
        if (config('cache.default')) {
            $events->listen(Loaded::class, [$this, 'cache']);
        }
    }

    public function cache(Loaded $event)
    {
        $key = sprintf('parts.%s.%d', get_class($event->part), $event->part->id);

        cache([$key => $event->part]);
    }
}
