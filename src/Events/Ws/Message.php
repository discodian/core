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
