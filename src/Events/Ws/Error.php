<?php

namespace Discodian\Core\Events\Ws;

use Discodian\Core\Events\Event;
use Exception;

class Error extends Event
{
    /**
     * @var Exception
     */
    public $e;

    public function __construct(Exception $e)
    {
        $this->e = $e;
    }
}
