<?php

namespace Discodian\Core\Extensions;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property string $name
 * @property string $version
 * @property string $version_normalized
 * @property string $path
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

    public static function new(array $package)
    {
        $extension = new static(Arr::only($package, ['name', 'version', 'version_normalized']));

        foreach (Arr::only($package, 'extra.discodian', []) as $extra => $value) {
            $extension->{$extra} = $value;
        }

        return $extension;
    }
}
