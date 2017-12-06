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

use Discodian\Core\Exceptions\InvalidEndpointException;
use Illuminate\Support\Arr;

trait InteractsWithApi
{
    /**
     * Api Endpoint definition of this Part.
     *
     * @var array
     */
    protected $endpoints = [];

    public function getEndpoint(string $endpoint) : string
    {
        $route = Arr::get($this->endpoints, $endpoint);

        if (! $route) {
            throw new InvalidEndpointException(sprintf('%s::%s', get_class($this), $endpoint));
        }

        // We need to replace some variables, eg :id
        if (preg_match_all('/:(?<attribute>[a-z_]+)/', $route, $m)) {
            foreach ($m['attributes'] as $key => $attribute) {
                $route = str_replace($m[0][$key], $this->get($attribute), $route);
            }
        }

        return $route;
    }
}
