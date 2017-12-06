<?php

namespace Discodian\Core\Events\Parts;

use Discodian\Core\Events\Event;

/**
 * @info Listeners to this event can return a Part.
 */
class Get extends Event
{
    /**
     * @var string
     */
    public $class;
    /**
     * @var string
     */
    public $id;

    public function __construct(string $class, string $id)
    {
        $this->class = $class;
        $this->id = $id;
    }
}
