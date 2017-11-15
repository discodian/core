<?php

namespace Discodian\Core\Events\Ws;

use Discodian\Core\Events\Event;

class Close extends Event
{
    /**
     * @var int
     */
    public $code;
    /**
     * @var string
     */
    public $reason;

    public function __construct(int $code, string $reason)
    {
        $this->code = $code;
        $this->reason = $reason;
    }
}
