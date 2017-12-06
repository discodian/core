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
use Discodian\Parts\Guild\Member;
use React\Promise\Deferred;

class GuildMemberAdd extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $memberPart = $this->factory->create(Member::class, $data);

        $guild = $this->factory->get(Guild::class, $memberPart->guild_id);

        if (! is_null($guild)) {
            $guild->members->push($memberPart);
            ++$guild->member_count;
            $this->factory->set($guild);
        }

        $deferred->resolve($memberPart);
    }
}
