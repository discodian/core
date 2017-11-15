<?php

namespace Discodian\Core\Events;

use Discodian\Core\Socket\Connector;

abstract class Event
{
    public static function send(array $data)
    {
        app(Connector::class)->send($data);
    }
}
