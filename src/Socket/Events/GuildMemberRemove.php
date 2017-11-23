<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Parts\User\Member;
use React\Promise\Deferred;

class GuildMemberRemove extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $memberPart = $this->factory->create(Member::class, $data, true);

        $guild = $this->discord->guilds->get('id', $memberPart->guild_id);

        if (! is_null($guild)) {
            $guild->members->pull($memberPart->id);
            --$guild->member_count;

            $this->discord->guilds->push($guild);
        }

        $deferred->resolve($memberPart);
    }
}
