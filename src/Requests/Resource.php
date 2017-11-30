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

use Discodian\Core\Database\Persists;
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
     * Whether resources retrieved should be stored in cache.
     *
     * @var bool
     */
    protected $caches = true;

    /**
     * Whether the resources retrieved should be stored in the database.
     *
     * @var bool
     */
    protected $persists = true;

    /**
     * Creates a Part based on attributes.
     *
     * @param array $data
     * @return Part
     */
    public function seed(array $data): Part
    {
        /** @var Part $part */
        $part = new $this->part($data);

        if ($this->caches) {
            cache(["resource.{$this->part}.{$part->id}" => $part]);
        }

        if ($this->persists && in_array(Persists::class, class_uses_recursive($part))) {
            $part->save();
        }

        return $part;
    }

    /**
     * @param string $part
     * @return Resource
     */
    public function setPart(string $part): Resource
    {
        $this->part = $part;

        return $this;
    }

    public function get($id)
    {
        $this->part->id = $id;
        $this->path = $this->part->getEndpoint('get');

        return $this->request();
    }
}
