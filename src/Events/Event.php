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
    public function send(array $data)
    {
        app(Connector::class)->send($data);
    }

    public function log(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }
}
