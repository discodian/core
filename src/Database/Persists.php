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

namespace Discodian\Core\Database;

trait Persists
{
    public function save(): bool
    {
        if (! config('database.default')) {
            return false;
        }

        return $this->persisted()->save();
    }

    public function delete()
    {
        return $this->persisted()->delete();
    }

    public function persisted(): Resource
    {
        return Resource::forPart($this);
    }
}
