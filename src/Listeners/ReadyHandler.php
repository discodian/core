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
use Discodian\Core\Resources\Bot;
use Discodian\Core\Resources\Guild\Guild;
use Discodian\Core\Socket\Requests\ResourceRequest;
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

        $bot = $this->bot($content->session_id, $content->user);

        $this->guilds($content->guilds);

        logs(sprintf("Bot \"%s:%s\" is ready on %d Guilds.",
            $bot->username,
            $bot->discriminator,
            count($content->guilds)
        ));

        logs("Bot \"{$bot->username}:{$bot->discriminator}\" is ready.");
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
        foreach ($guilds as $guild) {
            $resource = (new ResourceRequest())->setPart(Guild::class)->get($guild);
            dd($resource);
        }
    }
}
