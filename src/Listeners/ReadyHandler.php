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
use Discodian\Core\Socket\Events\GuildCreate;
use Discodian\Parts\Bot;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use React\Promise\Deferred;

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

        $bot = $this->bot($content->session_id, $content->user);

        $this->guilds($content->guilds);

        logs(sprintf("Bot \"%s:%s\" is ready on %d Guilds.",
            $bot->username,
            $bot->discriminator,
            count($content->guilds)
        ));
    }

    protected function bot(string $sessionId, $user): Bot
    {
        $bot = new Bot($user);
        $bot->session_id = $sessionId;

        $this->app->singleton(Bot::class, function () use ($bot) {
            return $bot;
        });

        return $this->app->make(Bot::class);
    }

    protected function guilds(array $guilds)
    {
        $unavailable = collect();
        $event = $this->app->make(GuildCreate::class);

        foreach ($guilds as $guild) {
            $defer = new Deferred;

            $defer->promise()->then(null, function ($payload) use (&$unavailable) {
                if ($payload[0] === 'unavailable') {
                    $unavailable->push($payload[1]);
                }
            });

            $event($defer, $guild);
        }
    }
}
