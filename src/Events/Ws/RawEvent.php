<?php

namespace Discodian\Core\Events\Ws;

use Discodian\Core\Events\Event;

abstract class RawEvent extends Event
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
