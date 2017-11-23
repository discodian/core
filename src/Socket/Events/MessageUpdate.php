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
use Discord\Parts\Channel\Message;
use Discord\Repository\Channel\MessageRepository;
use React\Promise\Deferred;

class MessageUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $messagePart = $this->factory->create(Message::class, $data, true);

        $messages = $this->discord->getRepository(
            MessageRepository::class,
            $messagePart->channel_id,
            'messages',
            ['channel_id' => $messagePart->channel_id]
        );
        $message = $messages->get('id', $messagePart->id);

        if (is_null($message)) {
            $newMessage = $messagePart;
        } else {
            $newMessage = $this->factory->create(Message::class, array_merge($message->getRawAttributes(), $messagePart->getRawAttributes()), true);
        }

        $old = $messages->get('id', $messagePart->id);
        $messages->push($newMessage);

        $deferred->resolve([$messagePart, $old]);
    }
}
