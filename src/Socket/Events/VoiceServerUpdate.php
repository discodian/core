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
use Discord\Parts\WebSockets\VoiceServerUpdate as VoiceServerUpdatePart;
use React\Promise\Deferred;

class VoiceServerUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, array $data)
    {
        $part = $this->factory->create(VoiceServerUpdatePart::class, $data, true);

        $deferred->resolve($part);
    }
}
