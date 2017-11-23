<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class GuildRoleDelete extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $guild = $this->discord->guilds->get('id', $data->guild_id);
        $guild->roles->pull($data->role_id);

        $deferred->resolve($data);
    }
}
