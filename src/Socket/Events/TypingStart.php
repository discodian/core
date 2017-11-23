<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Parts\WebSockets\TypingStart as TypingStartPart;
use React\Promise\Deferred;

class TypingStart extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $typing = $this->factory->create(TypingStartPart::class, $data, true);

        $deferred->resolve($typing);
    }
}
