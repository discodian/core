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

namespace Discodian\Core\Resources\Guild;

use Discodian\Core\Resources\Part;

/**
 * @property int $id
 * @property int $color
 * @property bool $hoist
 * @property bool $managed
 * @property string $name
 * @property bool $mentionable
 * @property int $permissions
 * @property int $position
 */
class Role extends Part
{
}
