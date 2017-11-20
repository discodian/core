<?php

namespace Discodian\Core\Database;

/**
 * @property bool $persisted
 */
trait Persists
{
    public function save(): bool
    {
        if (! config('database.default')) {
            return false;
        }

        $save = new SaveResource($this);

        return $save();
    }
}
