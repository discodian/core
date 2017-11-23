<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discodian\Parts\Guild\Guild;
use React\Promise\Deferred;

class GuildDelete extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $guildPart = $this->factory->create(Guild::class, $data, true);

        $this->discord->guilds->pull($guildPart->id);

        $deferred->resolve($guildPart);
    }
}
