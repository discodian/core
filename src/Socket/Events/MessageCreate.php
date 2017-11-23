<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Parts\Channel\Message;
use Discord\Repository\Channel\MessageRepository;
use React\Promise\Deferred;

class MessageCreate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $messagePart = $this->factory->create(Message::class, $data, true);

        if ($this->discord->options['storeMessages']) {
            $messages = $this->discord->getRepository(
                MessageRepository::class,
                $messagePart->channel_id,
                'messages',
                ['channel_id' => $messagePart->channel_id]
            );
            $messages->push($messagePart);
        }

        $deferred->resolve($messagePart);
    }
}
