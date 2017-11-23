<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Parts\Guild\Channel;
use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class ChannelDelete extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $channel = $this->factory->create(Channel::class, $data);

        $guild = $this->discord->guilds->get('id', $channel->guild_id);
        $guild->channels->pull($channel->id);

        $deferred->resolve($channel);
    }
}
