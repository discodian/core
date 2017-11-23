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

namespace Discodian\Core\Events\Log;

use Monolog\Handler\HandlerInterface;

class RegistersHandlers
{
    /**
     * @var array|HandlerInterface[]
     */
    public $handlers;

    public function __construct(array &$handlers)
    {
        $this->handlers = &$handlers;
    }
}
