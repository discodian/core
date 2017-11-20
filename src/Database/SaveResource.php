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

use Discodian\Core\Resources\Part;
use Illuminate\Support\Str;

class SaveResource
{
    public function __invoke(Part $part): bool
    {
        $model = new Resource($part->toArray());
        $model->setTable($this->table());

        return $model->save();
    }

    protected function table(Part $part): string
    {
        $class = get_class($part);
        $basename = basename($class);

        return Str::lower(Str::plural($basename));
    }
}
