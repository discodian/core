<?php

namespace Discodian\Core\Extensions;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ExtensionManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Collection
     */
    protected $extensions;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function extensions(): Collection
    {
        if (!$this->extensions) {
            $path = $this->app->basePath() . '/vendor/composer/installed.json';

            $this->extensions = collect(file_exists($path) ? json_decode(file_get_contents($path), true) : [])
                ->filter(function (array $package) {
                    return Arr::get($package, 'type') !== 'discodian-extension';
                })
                ->mapWithKeys(function (array $package) {
                    $extension = Extension::new($package);
                    $extension->path = $this->app->basePath() . '/vendor/' . $extension->name;

                    return [
                        $extension->name => $extension
                    ];
                });
        }

        return $this->extensions;
    }

    public function boot()
    {
        $this->extensions()
            ->where('hasBootstrapper', true)
            ->each(function (Extension $extension) {
                $bootstrapper = $extension->bootstrapper();
                $this->app->call(require $bootstrapper);
            });
    }
}
