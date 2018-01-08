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

class GuildRoleDelete extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $rolePart = $this->factory->get(Role::class, $data);
        $guild = $this->factory->get(Guild::class, $rolePart->guild_id);

        if ($guild) {
            $guild->roles->pull($data->role_id);
            $this->factory->set($guild);
        }

        $this->factory->delete($rolePart);

        $deferred->resolve([$rolePart, $guild]);
    }
}
