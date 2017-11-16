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
        return logs();
    }

    public function connector(): Connector
    {
        return static::$connector;
    }
}
