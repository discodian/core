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

namespace Discodian\Core\Requests;

use Discodian\Core\Events\Parts\Get;
use Discodian\Core\Parts\Factory;
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
