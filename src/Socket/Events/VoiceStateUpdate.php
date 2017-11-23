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

use Discodian\Core\Socket\Event;
use Discord\Parts\WebSockets\VoiceStateUpdate as VoiceStateUpdatePart;
use React\Promise\Deferred;

class VoiceStateUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $state = $this->factory->create(VoiceStateUpdatePart::class, $data, true);

        foreach ($this->discord->guilds as $index => $guild) {
            if ($guild->id == $state->guild_id) {
                foreach ($guild->channels as $cindex => $channel) {
                    $channel->members->pull($state->id);

                    if ($channel->id == $state->channel_id) {
                        $channel->members->push($state);
                    }
                }
            } else {
                $user = $this->discord->users->get('id', $state->id);

                foreach ($guild->channels as $cindex => $channel) {
                    if (! (isset($user) && $user->bot)) {
                        $channel->members->pull($state->id);
                    }
                }
            }
        }

        $deferred->resolve($state);
    }
}
