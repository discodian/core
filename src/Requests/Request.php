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

use Carbon\Carbon;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * @property int $rate_limit
 * @property int $rate_remaining
 * @property int $rate_reset
 */
abstract class Request
{
    protected $rateLimited = false;
    protected $queued = [];

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
     * @var RateLimiter
     */
    protected static $rateLimiter;

    protected static $endpointFailureCount = 0;

    public function requestBlocking()
    {
        $path = $this->getPath();

        try {
            $response = static::getHttp()->request($this->method, $path, $this->buildParams());
        } catch (RequestException $e) {
            if ($e->getCode() === 401) {
                logs($e->getMessage());

                // quit application running
                exit(254);
            }
        }

        $this->processRateLimits($path, $response);
        $this->preventRateLimiting($path, $response);

        return $this->parseResponseBody($response);
    }

    /**
     * @return \React\Promise\Promise
     */
    public function request()
    {
        $promise = null;

        $path = $this->getPath();

        $request = function () use (&$request, $path, &$promise) {
            $promise = static::getHttp()->requestAsync($this->method, $path, $this->buildParams());

            $promise->then(function (Response $response) use (&$request, $path, $promise) {
                $this->processRateLimits($path, $response);
                $this->preventRateLimiting($path, $response, $request);
                $this->handleEndpointFailures($response, $promise, $request);

                $output = $this->parseResponseBody($response);

                $promise->resolve($output);
            }, function (Exception $e) use ($promise) {
                logs("Request failed {$e->getMessage()}", $e->getTrace());
                $promise->reject($e);
            });
        };

        if ($this->rateLimited) {
            $defer = new Deferred();
            $defer->promise()->then($request());
            $this->queued[] = $promise;
        } else {
            $request();
        }

        return $promise;
    }

    /**
     * @param Response $response
     * @return Response|mixed|null
     */
    protected function parseResponseBody(Response $response)
    {
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
    }

    /**
     * @param Response $response
     * @param          $defer
     * @param          $request
     */
    protected function handleEndpointFailures(Response $response, $defer, $request)
    {
        $code = $response->getStatusCode();

        if (in_array($code, [502, 525])) {
            static::$endpointFailureCount++;

            if (static::$endpointFailureCount >= 5) {
                logs("Discord endpoint failure 502 or 525, drop out after 5 attempts.");
                exit(254);
            }

            static::getLoop()->addTimer(0.25, $request);
        } elseif (static::$endpointFailureCount > 0) {
            static::$endpointFailureCount = 0;
        }

        if ($code < 200 || $code >= 300) {
            $defer->reject($response);
        }
    }

    /**
     * @param string $path
     * @param Response $response
     * @param $request
     * @return bool
     */
    protected function preventRateLimiting(string $path, Response $response, $request = null): bool
    {
        /** @var RateLimitation $rateLimitation */
        $rateLimitation = static::getRateLimiter()->getLimit($path);

        if ($rateLimitation && $rateLimitation->active) {
            $this->rateLimited = true;

            if ($rateLimitation->retry) {
                $waitFor = $rateLimitation->retry/1000;
                logs("Throttling request for $path, retry in {$waitFor} seconds.");
            } else {
                $waitFor = Carbon::now()->diffInSeconds(Carbon::createFromTimestamp($rateLimitation->resets));
                logs("Throttling request for $path, resets {$rateLimitation->resetsDiffHuman()}.");
            }

            // In case we hit the rate limit already, put the current request back on the stack.
            if ($request && $response->getStatusCode() === 429) {
                $this->queued = $request;
            }

            static::getLoop()->addTimer($waitFor, function () {
                foreach ($this->queued as $i => $item) {
                    $item->resolve();
                    unset($this->queued[$i]);
                }

                $this->rateLimited = false;
            });

            return true;
        }

        return false;
    }

    /**
     * @param string   $path
     * @param Response $response
     */
    protected function processRateLimits(string $path, Response $response)
    {
        $headers = new HeaderBag($response->getHeaders());

        static::getRateLimiter()->processIncomingHeaders($path, $headers, $response);
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

    public static function getRateLimiter(): RateLimiter
    {
        if (!static::$rateLimiter) {
            static::$rateLimiter = app(RateLimiter::class);
        }

        return static::$rateLimiter;
    }

    protected static function getLoop(): LoopInterface
    {
        return app(LoopInterface::class);
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
