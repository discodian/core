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
use Illuminate\Support\Fluent;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * @property int $rate_limit
 * @property int $rate_remaining
 * @property int $rate_reset
 */
abstract class Request extends Fluent
{
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
    protected static $client;

    final public function request()
    {
        $response = static::getClient()->request($this->method, $this->path);

        $this->processRateLimits(new HeaderBag($response->getHeaders()));

        if ($response->getStatusCode() === 200) {
            $payload = $response->getBody()->getContents();
            $payload = \json_decode($payload, true);

            return $payload;
        } else {
        }
    }

    final protected function processRateLimits(HeaderBag $headers)
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

    public static function setClient(ClientInterface $client)
    {
        static::$client = $client;
    }

    public static function getClient(): ClientInterface
    {
        if (! static::$client) {
            static::$client = app(ClientInterface::class);
        }

        return static::$client;
    }
}
