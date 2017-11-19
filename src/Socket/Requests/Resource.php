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

namespace Discodian\Core\Socket\Requests;

use Discodian\Core\Resources\Part;

abstract class Resource extends Request
{
    /**
     * The part this request is made on behalf of.
     *
     * @var string
     */
    protected $part;

    protected function seed(array $data): Part
    {
        $part = new $this->part($data);

        return $part;
    }
}
