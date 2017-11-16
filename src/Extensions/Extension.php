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

namespace Discodian\Core\Extensions;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property string $name
 * @property string $version
 * @property string $version_normalized
 * @property string $path
 * @property boolean $enabled
 */
class Extension extends Fluent
{
    public function hasBootstrapper(): bool
    {
        return file_exists($this->bootstrapper());
    }

    public function bootstrapper(): string
    {
        return $this->path . '/bootstrap.php';
    }

    /**
     * @param array $package installed.json package entry.
     * @return static
     */
    public static function new(array $package)
    {
        $extension = new static(Arr::only($package, ['name', 'version', 'version_normalized']));

        foreach (Arr::only($package, 'extra.discodian', []) as $extra => $value) {
            $extension->{$extra} = $value;
        }

        $extension->enabled = in_array($extension->name, config('extensions.enabled', []));

        return $extension;
    }
}
