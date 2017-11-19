<?php

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
