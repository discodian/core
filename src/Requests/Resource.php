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

use Discodian\Parts\Part;

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
     * @return static
     */
    public function setPart(string $part)
    {
        $this->part = $part;

        return $this;
    }

    public function get($id): Part
    {
        /** @var Part $part */
        $part = app()->make($this->part);
        $part->id = $id;
        $this->path = $part->getEndpoint('get');

        $promise = $this->request();

        $promise->then(function ($response) use (&$part) {
            foreach($response as $attribute => $value) {
                $part->{$attribute} = $value;
            }
        });

        $promise->resolve($part);

        return $part;
    }
}
