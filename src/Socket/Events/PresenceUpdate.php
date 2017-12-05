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
use Discodian\Parts\Socket\PresenceUpdate as PresenceUpdatePart;
use React\Promise\Deferred;

class PresenceUpdate extends Event
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Deferred $deferred, \stdClass $data)
    {
        $presenceUpdate = $this->factory->create(PresenceUpdatePart::class, $data);
        $old            = null;

        $guild  = $this->discord->guilds->get('id', $presenceUpdate->guild_id);
        $member = $guild->members->get('id', $presenceUpdate->user->id);

        if (! is_null($member)) {
            $rawOld = array_merge([
                'roles'  => [],
                'status' => null,
                'game'   => null,
                'nick'   => null,
            ], $member->getRawAttributes());

            $old = $this->factory->create(PresenceUpdatePart::class, [
                'user'     => $this->discord->users->get('id', $presenceUpdate->user->id),
                'roles'    => $rawOld['roles'],
                'guild_id' => $presenceUpdate->guild_id,
                'status'   => $rawOld['status'],
                'game'     => $rawOld['game'],
                'nick'     => $rawOld['nick'],
            ], true);

            $presenceAttributes = $presenceUpdate->getRawAttributes();
            $member->fill([
                'status' => $presenceAttributes['status'],
                'roles'  => $presenceAttributes['roles'],
                'nick'   => $presenceAttributes['nick'],
                'game'   => $presenceAttributes['game'],
            ]);

            $guild->members->push($member);
        }

        $deferred->resolve([$presenceUpdate, $old]);
    }
}
