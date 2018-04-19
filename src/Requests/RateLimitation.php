<?php

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
