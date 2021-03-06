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

use Discodian\Parts\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Resource extends Model
{
    protected $fillable = ['*'];

    /**
     * @var Part
     */
    protected $part;

    public function getTable(): string
    {
        $class = get_class($this->part);
        $class = Str::lower(Str::plural($class));
        $table = last(explode('\\', $class));

        return $table;
    }

    /**
     * @param Part $part
     * @return Resource
     */
    public static function forPart(Part $part): Resource
    {
        $resource = static::newQuery()->findOrNew($part->id, $part->toArray());

        return $resource->setPart($part);
    }

    /**
     * @return Part
     */
    public function getPart(): Part
    {
        return $this->part;
    }

    /**
     * @param Part $part
     * @return Resource
     */
    public function setPart(Part $part): Resource
    {
        $this->part = $part;

        return $this;
    }
}
