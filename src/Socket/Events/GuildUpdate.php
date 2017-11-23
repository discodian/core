<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Guild\Role;
use Discord\Repository\Guild\RoleRepository;
use React\Promise\Deferred;

class GuildUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        if (isset($data->unavailable) && $data->unavailable) {
            $deferred->notify('Guild is unavailable.');

            return;
        }

        $guildPart = $this->factory->create(Guild::class, $data, true);

        $roles = new RoleRepository(
            $this->http,
            $this->cache,
            $this->factory
        );

        foreach ($data->roles as $role) {
            $role             = (array) $role;
            $role['guild_id'] = $guildPart->id;
            $rolePart         = $this->factory->create(Role::class, $role, true);

            $roles->push($rolePart);
        }

        $guildPart->roles = $roles;

        if ($guildPart->large) {
            $this->discord->addLargeGuild($guildPart);
        }

        $old = $this->discord->guilds->get('id', $guildPart->id);
        $this->discord->guilds->push($guildPart);

        $deferred->resolve([$guildPart, $old]);
    }
}
