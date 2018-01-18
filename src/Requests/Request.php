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
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
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
    protected $rate_limit = [];
    protected $rate_reset = [];
    protected $rate_remaining = [];
    protected $retry_after;

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
        $defer = new Deferred();

        $path = $this->getPath();

        $request = function () use (&$request, $path, $defer) {
            $promise = static::getHttp()->requestAsync($this->method, $path, $this->buildParams());

            $promise->then(function (Response $response) use (&$request, $path, $defer) {
                $this->processRateLimits($path, new HeaderBag($response->getHeaders()));
                $this->preventRateLimiting($path, $response, $request);
                $this->handleEndpointFailures($response, $defer, $request);

                if ($response->getHeaderLine('content-type') === 'application/json') {
                    $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    if (Arr::get($response, 'state') === 'fulfilled') {
                        $output = Arr::get($response, 'value', []);
                    }
                } else {
                    $output = $response;
                }

                $defer->resolve($output);
            }, function (\Exception $e) use ($defer) {
                logs("Request failed {$e->getMessage()}", $e->getTrace());
                $defer->reject($e);
            });
        };

        if ($this->rateLimited) {
            $defer = new Deferred();
            $defer->promise()->then($request());
            $this->queued[] = $defer;
        } else {
            $request();
        }

        return $defer->promise();
    }

    protected function handleEndpointFailures(Response $response, Deferred $defer, $request)
    {
        $code = $response->getStatusCode();
        if (in_array($code, [502, 525])) {
            static::getLoop()->addTimer(0.25, $request);
        }

        if ($code < 200 || $code > 226) {
            $defer->reject($response);
        }
    }

    /**
     * @param string $path
     * @param Response $response
     * @param $request
     * @return bool
     */
    protected function preventRateLimiting(string $path, Response $response, $request): bool
    {
        $remaining = Arr::get($this->rate_remaining, $path);
        $resetsAt = Arr::get($this->rate_reset, $path);

        if ($response->getStatusCode() === 429 || (
            $remaining !== null && $resetsAt !== null && $remaining === 0)) {
            $this->rateLimited = true;

            if (!$resetsAt && $this->retry_after) {
                $waitFor = $this->retry_after/1000;
            } else {
                $waitFor = Carbon::now()->diffInSeconds(Carbon::createFromTimestamp($resetsAt));
            }

            // In case we hit the rate limit already, put the current request back on the stack.
            if ($response->getStatusCode() === 429) {
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
     * @param string $path
     * @param HeaderBag $headers
     */
    protected function processRateLimits(string $path, HeaderBag $headers)
    {
        if ($headers->has('x-ratelimit-limit')) {
            $this->rate_limit[$path] = $headers->get('x-ratelimit-limit');
        }

        if ($headers->has('x-ratelimit-reset')) {
            $this->rate_reset[$path] = $headers->get('x-ratelimit-reset');
        }

        if ($headers->has('x-ratelimit-remaining')) {
            $this->rate_remaining[$path] = $headers->get('x-ratelimit-remaining');
        }

        $this->retry_after = $headers->get('retry-after');
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
