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
use Discodian\Parts\Channel\Message;
use React\Promise\Deferred;

class MessageUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $message = $this->factory->get(Message::class, $data->d->id);

        $messagePart = $this->factory->create(Message::class, $data);


        if (is_null($message)) {
            $newMessage = $messagePart;
        } else {
            $newMessage = $this->factory->create(Message::class, array_merge($message->getAttributes(), $messagePart->getAttributes()));
        }

        $this->factory->set($newMessage);

        $deferred->resolve([$messagePart, $message]);
    }
}
