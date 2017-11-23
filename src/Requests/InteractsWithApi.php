<?php

namespace Discodian\Core\Requests;

use Discodian\Core\Exceptions\InvalidEndpointException;
use Illuminate\Support\Arr;
use function React\Promise\reject;

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
            reject(new InvalidEndpointException($endpoint));
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
