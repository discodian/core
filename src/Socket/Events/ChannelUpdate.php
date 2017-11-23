<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Parts\Guild\Channel;
use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class ChannelUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $channel = $this->factory->create(Channel::class, $data, true);

        if ($channel->is_private) {
            $old = $this->discord->private_channels->get('id', $channel->id);
            $this->discord->private_channels->push($channel);
        } else {
            $guild = $this->discord->guilds->get('id', $channel->guild_id);
            $old   = $guild->channels->get('id', $channel->id);
            $guild->channels->push($channel);
        }

        $deferred->resolve([$channel, $old]);
    }
}
