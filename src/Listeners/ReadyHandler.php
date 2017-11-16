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

namespace Discodian\Core\Listeners;

use Discodian\Core\Events\Ws\Ready;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

class ReadyHandler
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Ready::class, [$this, 'ready']);
    }

    public function ready(Ready $event)
    {
        logs("Ready received.");

        $content = $event->data->d;

        $this->bot($content->session_id, $content->user);
    }

    protected function bot(string $sessionId, $user)
    {
        dd($sessionId, $user);
    }
}
