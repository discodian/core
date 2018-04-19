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

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\HeaderBag;

class RateLimiter
{
    /**
     * @var Collection|RateLimitation[]
     */
    protected $limits;

    public function __construct()
    {
        $this->limits = new Collection();
    }

    public function processIncomingHeaders(string $path, HeaderBag $headers)
    {
        /** @var RateLimitation $limitation */
        $limitation = $this->limits->get($path, new RateLimitation());

        $limitation->path = $path;
        $limitation->global = $headers->has('x-ratelimit-global');

        if ($limitation->global) {
            $this->limits->put($path, $limitation);

            $limitation = $this->global();
        }

        if ($headers->has('x-ratelimit-limit')) {
            $limitation->limit = (int) $headers->get('x-ratelimit-limit');
        }

        if ($headers->has('x-ratelimit-reset')) {
            $limitation->resets = (int) $headers->get('x-ratelimit-reset');
        }

        if ($headers->has('x-ratelimit-remaining')) {
            $limitation->remaining = (int) $headers->get('x-ratelimit-remaining');
        }

        $limitation->retry = $headers->get('retry-after');

        $limitation->active = $limitation->retry !== null || $limitation->remaining === 0;

        $this->limits->put(
            $limitation->path,
            $limitation
        );
    }

    public function getLimit(string $path): ?RateLimitation
    {
        /** @var RateLimitation $pathLimit */
        $pathLimit = $this->limits->get($path);

        if ($pathLimit && $pathLimit->global) {
            return $this->global();
        }

        return $pathLimit;
    }

    public function global(): RateLimitation
    {
        return $this->limits->get('/', new RateLimitation([
            'path' => '/',
            'limit' => 10,
            'remaining' => 10
        ]));
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->limits, $name)) {
            return $this->limits->{$name}($arguments);
        }
    }
}
