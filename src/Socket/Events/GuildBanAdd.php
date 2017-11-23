<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class GuildBanAdd extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $guild = $this->discord->guilds->get('id', $data->guild_id);
        $ban   = $this->factory->create(Ban::class, [
            'guild' => $guild,
            'user'  => $data->user,
        ], true);

        $guild = $this->discord->guilds->get('id', $ban->guild->id);
        $guild->bans->push($ban);

        $deferred->resolve($ban);
    }
}
