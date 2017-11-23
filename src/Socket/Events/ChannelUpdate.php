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

namespace Discodian\Core\Socket\Events;

use Discodian\Parts\Guild\Channel;
use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class ChannelUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
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
