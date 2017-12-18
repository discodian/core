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

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * @property int $rate_limit
 * @property int $rate_remaining
 * @property int $rate_reset
 */
abstract class Request
{
    protected $rate_limit;
    protected $rate_reset;
    protected $rate_remaining;

    /**
     * Path to send request to.
     *
     * @var string
     */
    protected $path;

    /**
     * Request method
     *
     * @var string
     */
    protected $method = 'get';

    /**
     * JSON params to sent along.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Properties of the object to work with.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @var ClientInterface
     */
    protected static $http;

    /**
     * @return Promise
     */
    public function request()
    {
        return static::getHttp()
            ->requestAsync($this->method, $this->getPath(), $this->buildParams())
            ->then(function (Response $response) {
                $this->processRateLimits(new HeaderBag($response->getHeaders()));

                $output = null;

                if ($response->getHeaderLine('content-type') === 'application/json') {
                    $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    if (Arr::get($response, 'state') === 'fulfilled') {
                        $output = Arr::get($response, 'value', []);
                    }
                } else {
                    $output = $response;
                }

                return $output;
            }, function (\Exception $e) {
                logs("Request failed {$e->getMessage()}", $e->getTrace());
            });
    }

    /**
     * @param HeaderBag $headers
     */
    protected function processRateLimits(HeaderBag $headers)
    {
        if ($headers->has('x-ratelimit-limit')) {
            $this->rate_limit = $headers->get('x-ratelimit-limit');
        }

        if ($headers->has('x-ratelimit-reset')) {
            $this->rate_reset = $headers->get('x-ratelimit-reset');
        }

        if ($headers->has('x-ratelimit-remaining')) {
            $this->rate_remaining = $headers->get('x-ratelimit-remaining');
        }
    }

    /**
     * @param ClientInterface $client
     */
    public static function setHttp(ClientInterface $client)
    {
        static::$http = $client;
    }

    /**
     * @return ClientInterface
     */
    public static function getHttp(): ClientInterface
    {
        if (!static::$http) {
            static::$http = app(ClientInterface::class);
        }

        return static::$http;
    }

    protected function buildParams(): array
    {
        return empty($this->params) ? [] : [
            'json' => $this->params
        ];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return path_injection($this->path, $this->properties);
    }
}
