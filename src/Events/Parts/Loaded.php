<?php

namespace Discodian\Core\Events\Parts;

use Discodian\Core\Events\Event;
use Discodian\Parts\Part;

class Loaded extends Event
{
    /**
     * @var Part
     */
    public $part;

    public function __construct(Part $part)
    {
        $this->part = $part;
    }
}
