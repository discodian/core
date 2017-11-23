<?php

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discord\Repository\Channel\MessageRepository;
use React\Promise\Deferred;

class MessageDeleteBulk extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $messages = $this->discord->getRepository(
            MessageRepository::class,
            $data->channel_id,
            'messages',
            ['channel_id' => $data->channel_id]
        );

        foreach ($data->ids as $message) {
            $messages->pull($message);
        }

        $deferred->resolve($data);
    }
}
