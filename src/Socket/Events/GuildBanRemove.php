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
use React\Promise\Deferred;

class GuildBanRemove extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $guild = $this->discord->guilds->get('id', $data->guild_id);
        $ban   = $this->factory->create(Ban::class, [
            'guild' => $guild,
            'user'  => $data->user,
        ], true);

        $guild = $this->discord->guilds->get('id', $ban->guild->id);
        $guild->bans->pull($ban->id);

        $deferred->resolve($ban);
    }
}
