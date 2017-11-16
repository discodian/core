<?php

namespace Discodian\Core\Events;

use Discodian\Core\Socket\Connector;
use Psr\Log\LoggerInterface;

abstract class Event
{
    /**
     * @var Connector
     */
    protected static $connector;

    /**
     * @param Connector $connector
     */
    public static function setConnector(Connector $connector)
    {
        self::$connector = $connector;
    }

    public function send(array $data)
    {
        static::$connector->send($data);
    }

    public function log(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }

    public function connector(): Connector
    {
        return static::$connector;
    }
}
