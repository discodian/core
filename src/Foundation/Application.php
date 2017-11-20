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

namespace Discodian\Core\Foundation;

use Discodian\Core\Extensions\ExtensionManager;
use Discodian\Core\Providers\CacheProvider;
use Discodian\Core\Providers\DatabaseProvider;
use Discodian\Core\Providers\EventProvider;
use Discodian\Core\Providers\HttpProvider;
use Discodian\Core\Providers\LogProvider;
use Discodian\Core\Providers\SocketProvider;
use Discodian\Core\Socket\Connector;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContact;
use Illuminate\Contracts\Foundation\Application as Contract;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;

class Application extends Container implements Contract
{
    const VERSION = '0.1';
    /**
     * @var string
     */
    protected $basePath;

    protected $bootingCallbacks = [];
    protected $bootedCallbacks = [];
    protected $booted = false;

    public function __construct(string $basePath)
    {
        $this->basePath = realpath($basePath);
        $this->loadEnv();

        $this->setupCoreBindings();
        $this->setupConfiguration();
        $this->registerConfiguredProviders();

        $this->singleton(Connector::class);
    }

    protected function setupCoreBindings()
    {
        static::setInstance($this);
        Facade::setFacadeApplication($this);

        $this->instance(Container::class, $this);
        $this->alias(Container::class, Contract::class);
        $this->alias(Container::class, ContainerContact::class);

        $this->singleton(
            \Illuminate\Contracts\Config\Repository::class,
            \Illuminate\Config\Repository::class
        );

        $this->alias(\Illuminate\Contracts\Config\Repository::class, 'config');

        $this->singleton(ExtensionManager::class);

        $this->alias(\GuzzleHttp\Client::class, \GuzzleHttp\ClientInterface::class);

        $this->singleton(\Illuminate\Contracts\Events\Dispatcher::class, \Illuminate\Events\Dispatcher::class);

        $this->alias(\Illuminate\Contracts\Events\Dispatcher::class, 'events');

        $this->alias(\Symfony\Component\Console\Output\ConsoleOutput::class, \Symfony\Component\Console\Output\ConsoleOutputInterface::class);

        $this->bind(\Illuminate\Contracts\Filesystem\Filesystem::class, \Illuminate\Filesystem\Filesystem::class);
        $this->alias(\Illuminate\Contracts\Filesystem\Filesystem::class, 'files');
    }

    protected function setupConfiguration()
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->make('config');
        foreach (new \DirectoryIterator($this->configPath()) as $file) {
            if ($file->getExtension() === 'php' && $path = $file->getRealPath()) {
                $config->set($file->getBasename('.php'), include $path);
            }
        }
    }


    /**
     * Loads environment variables set in .env.
     */
    protected function loadEnv()
    {
        try {
            (new Dotenv($this->basePath))->load();
        } catch (InvalidPathException $e) {
            // ..
        }
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version(): string
    {
        return static::VERSION;
    }

    public function userAgent(): string
    {
        return 'Discodian/' . $this->version();
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get the path for the stored configuration files.
     *
     * @return string
     */
    public function configPath(): string
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Get or check the current application environment.
     *
     * @return string|bool
     */
    public function environment()
    {
        $env = $this['config']->get('core.environment');

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }
            return false;
        }

        return $env;
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        foreach ([
                     LogProvider::class,
                     HttpProvider::class,
                     EventProvider::class,
                     SocketProvider::class,
                     CacheProvider::class,
                     DatabaseProvider::class,
                 ] as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @param  array $options
     * @param  bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        $provider = new $provider($this);
        $this->call([$provider, 'register']);
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string $provider
     * @param  string|null $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // TODO: Implement registerDeferredProvider() method.
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->fireAppCallback($this->bootingCallbacks);

        /** @var ExtensionManager $manager */
        $manager = $this->make(ExtensionManager::class);

        $manager->boot();

        $this->fireAppCallback($this->bootedCallbacks);
    }

    protected function fireAppCallback(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Register a new boot listener.
     *
     * @param  mixed $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        // TODO: Implement getCachedServicesPath() method.
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        // TODO: Implement getCachedPackagesPath() method.
    }

    public function run()
    {
        /** @var Connector $connector */
        $connector = $this->make(Connector::class);

        return $connector->run();
    }
}
