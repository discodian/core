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

namespace Discodian\Core\Socket\Events;

use Discodian\Core\Socket\Event;
use Discodian\Parts\Guild\Ban;
use React\Promise\Deferred;

class GuildBanAdd extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $ban = $this->factory->create(Ban::class, $data);

        $deferred->resolve($ban);
    }
}
