<?php

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
