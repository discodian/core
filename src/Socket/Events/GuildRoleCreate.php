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
use Discodian\Parts\Guild\Role;
use React\Promise\Deferred;

class GuildRoleCreate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $adata             = (array) $data->role;
        $adata['guild_id'] = $data->guild_id;

        $rolePart = $this->factory->create(Role::class, $adata);

        /** @var Guild $guild */
        $guild = $this->factory->get(Guild::class, $data->guild_id);

        if (! is_null($guild)) {
            $guild->roles->push($rolePart);
            $this->factory->set($guild);
        }

        $deferred->resolve($rolePart);
    }
}
