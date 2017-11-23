<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Parts\Guild\Channel;
use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class ChannelCreate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $channel = $this->factory->create(Channel::class, $data, true);

        if (array_search($channel->type, [Channel::TYPE_TEXT, Channel::TYPE_VOICE]) === false) {
            $this->discord->private_channels->push($channel);
        } else {
            $guild = $this->discord->guilds->get('id', $channel->guild_id);
            $guild->channels->push($channel);
        }

        $deferred->resolve($channel);
    }
}
