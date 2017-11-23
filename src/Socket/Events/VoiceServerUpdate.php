<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Parts\WebSockets\VoiceServerUpdate as VoiceServerUpdatePart;
use React\Promise\Deferred;

class VoiceServerUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $part = $this->factory->create(VoiceServerUpdatePart::class, $data, true);

        $deferred->resolve($part);
    }
}
