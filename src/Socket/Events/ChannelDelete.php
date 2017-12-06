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

use Discodian\Parts\Channel\Channel;
use Discodian\Core\Socket\Event;
use React\Promise\Deferred;

class ChannelDelete extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $channel = $this->factory->create(Channel::class, $data);

        $this->factory->delete($channel);

        $deferred->resolve($channel);
    }
}
