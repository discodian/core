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
use Discodian\Parts\Guild\Channel;
use Discodian\Parts\Guild\Guild;
use Discodian\Parts\Guild\Ban;
use Discodian\Parts\Guild\Role;
use Discodian\Parts\Guild\Member;
use Discord\Parts\WebSockets\VoiceStateUpdate as VoiceStateUpdatePart;
use React\Promise\Deferred;

class GuildCreate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        if (isset($data->unavailable) && $data->unavailable) {
            $deferred->reject(['unavailable', $data->id]);

            return $deferred->promise();
        }

        $guildPart = $this->factory->create(Guild::class, $data, true);

        $roles = new RoleRepository(
            $this->http,
            $this->cache,
            $this->factory,
            $guildPart->getRepositoryAttributes()
        );

        foreach ($data->roles as $role) {
            $role             = (array) $role;
            $role['guild_id'] = $guildPart->id;
            $rolePart         = $this->factory->create(Role::class, $role, true);

            $roles->push($rolePart);
        }

        $channels = new ChannelRepository(
            $this->http,
            $this->cache,
            $this->factory,
            $guildPart->getRepositoryAttributes()
        );

        foreach ($data->channels as $channel) {
            $channel             = (array) $channel;
            $channel['guild_id'] = $data->id;
            $channelPart         = $this->factory->create(Channel::class, $channel, true);

            $channels->push($channelPart);
        }

        $members = new MemberRepository(
            $this->http,
            $this->cache,
            $this->factory,
            $guildPart->getRepositoryAttributes()
        );

        foreach ($data->members as $member) {
            $memberPart = $this->factory->create(Member::class, [
                'user'      => $member->user,
                'roles'     => $member->roles,
                'mute'      => $member->mute,
                'deaf'      => $member->deaf,
                'joined_at' => $member->joined_at,
                'nick'      => (property_exists($member, 'nick')) ? $member->nick : null,
                'guild_id'  => $data->id,
                'status'    => 'offline',
                'game'      => null,
            ], true);

            foreach ($data->presences as $presence) {
                if ($presence->user->id == $member->user->id) {
                    $memberPart->status = $presence->status;
                    $memberPart->game   = $presence->game;
                }
            }

            $this->discord->users->push($memberPart->user);
            $members->push($memberPart);
        }

        $guildPart->roles    = $roles;
        $guildPart->channels = $channels;
        $guildPart->members  = $members;

        foreach ($data->voice_states as $state) {
            if ($channel = $guildPart->channels->get('id', $state->channel_id)) {
                $channel->members->push($this->factory->create(VoiceStateUpdatePart::class, (array) $state, true));
            }
        }

        $resolve = function () use (&$guildPart, $deferred) {
            if ($guildPart->large) {
                $this->discord->addLargeGuild($guildPart);
            }

            $this->discord->guilds->push($guildPart);

            $deferred->resolve($guildPart);
        };

        if (false) {
            $this->http->get("guilds/{$guildPart->id}/bans")->then(function ($rawBans) use (&$guildPart, $resolve) {
                $bans = new BanRepository(
                    $this->http,
                    $this->cache,
                    $this->factory,
                    $guildPart->getRepositoryAttributes()
                );

                foreach ($rawBans as $ban) {
                    $ban = (array) $ban;
                    $ban['guild'] = $guildPart;

                    $banPart = $this->factory->create(Ban::class, $ban, true);

                    $bans->push($banPart);
                }

                $guildPart->bans = $bans;
                $resolve();
            }, $resolve);
        } else {
            $resolve();
        }
    }
}
