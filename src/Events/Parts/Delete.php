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
use Discodian\Parts\Part;

class Delete extends Event
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
