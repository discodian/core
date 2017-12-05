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

namespace Discodian\Core\Requests;

use GuzzleHttp\Promise\Promise;

class Resource extends Request
{
    /**
     * The part this request is made on behalf of.
     *
     * @var string
     */
    protected $part;

    /**
     * @param string $part
     * @return Resource
     */
    public function setPart(string $part): Resource
    {
        $this->part = $part;

        return $this;
    }

    public function get($id): Promise
    {
        $this->part->id = $id;
        $this->path = $this->part->getEndpoint('get');

        return $this->request();
    }
}
