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

use GuzzleHttp\ClientInterface;
use React\Promise\Promise;
use React\Promise\Deferred;
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
     * @var string
     */
    protected $method = 'get';

    /**
     * @var ClientInterface
     */
    protected static $http;

    /**
     * @param string|null $method
     * @param string|null $path
     * @return Promise
     */
    public function request(string $method = null, string $path = null)
    {
        $defer = new Deferred();

        static::getHttp()
            ->requestAsync($method ?? $this->method, $path ?? $this->path)
            ->then(function ($response) use (&$defer) {
                $this->processRateLimits(new HeaderBag($response->getHeaders()));

                $defer->resolve($response);
            }, function ($e) use ($defer) {
                $defer->reject($e);
            });

        return $defer->promise();
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
}
