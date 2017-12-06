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
