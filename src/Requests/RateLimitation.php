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
use Illuminate\Support\Fluent;

/**
 * @property string $path
 * @property int $limit
 * @property int $remaining
 * @property int $resets
 * @property int $retry
 * @property bool $global
 * @property bool $active
 */
class RateLimitation extends Fluent
{
    public function resetsDiffHuman(): ?string
    {
        return $this->resets ? Carbon::createFromTimestamp($this->resets)->diffForHumans() : null;
    }
}
