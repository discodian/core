<?php

namespace Discodian\Core\Events\Ws;

use Discodian\Core\Events\Event;
use Ratchet\RFC6455\Messaging\Message as IncomingMessage;

class Message extends Event
{
    /**
     * @var IncomingMessage
     */
    public $message;

    public function __construct(IncomingMessage $message)
    {
        $this->message = $message;
    }
}
