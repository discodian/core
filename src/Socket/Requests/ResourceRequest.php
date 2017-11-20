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

use Discodian\Core\Database\Persists;
use Discodian\Core\Resources\Part;
use Illuminate\Support\Arr;

class ResourceRequest extends Request
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
     * @return ResourceRequest
     */
    public function setPart(string $part): ResourceRequest
    {
        $this->part = $part;
        return $this;
    }

    /**
     * @return array
     */
    public function getHttpEndpoints(): array
    {
        $config = config('discord.http.endpoints', []);

        return Arr::get($config, $this->part);
    }

    public function callEndpoint($name, $arguments)
    {
        if ($endpoint = Arr::get($this->getHttpEndpoints(), $name)) {
            dd($endpoint);
        }
    }

    public function __call($name, $arguments)
    {
        if ($response = $this->callEndpoint($name, $arguments)) {
            return $response;
        }

        return parent::__call($name, $arguments);
    }
}
