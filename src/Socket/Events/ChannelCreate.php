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
