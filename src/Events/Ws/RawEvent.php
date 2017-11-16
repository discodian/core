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

abstract class RawEvent extends Event
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
