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
use Discodian\Parts\Guild\Guild;
use Discodian\Parts\Socket\VoiceStateUpdate as VoiceStateUpdatePart;
use Discodian\Parts\User\User;
use React\Promise\Deferred;

class VoiceStateUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $state = $this->factory->create(VoiceStateUpdatePart::class, $data);

        $guilds = $this->factory->all(Guild::class);

        $guilds->each(function (Guild $guild) use ($state) {
            if ($guild->id == $state->guild_id) {
                foreach ($guild->channels as $cindex => $channel) {
                    $channel->members->pull($state->id);

                    if ($channel->id == $state->channel_id) {
                        $channel->members->push($state);
                    }
                }
            } else {
                $user = $this->factory->get(User::class, $state->id);

                foreach ($guild->channels as $cindex => $channel) {
                    if (! (isset($user) && $user->bot)) {
                        $channel->members->pull($state->id);
                    }
                }
            }
            $this->factory->set($guild);
        });

        $deferred->resolve($state);
    }
}
