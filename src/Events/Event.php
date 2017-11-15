<?php

namespace Discodian\Core\Events;

use Discodian\Core\Socket\Connector;
use Psr\Log\LoggerInterface;

abstract class Event
{
    public function send(array $data)
    {
        app(Connector::class)->send($data);
    }

    public function log(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }
}
