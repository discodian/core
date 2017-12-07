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

use Illuminate\Container\Container;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed|\Illuminate\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }
        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed|\Illuminate\Cache\CacheManager
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();
        if (empty($arguments)) {
            return app('cache');
        }
        if (is_string($arguments[0])) {
            return app('cache')->get($arguments[0], $arguments[1] ?? null);
        }
        if (! is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }
        if (! isset($arguments[1])) {
            throw new Exception(
                'You must specify an expiration time when setting a value in the cache.'
            );
        }
        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }
        if (is_array($key)) {
            return app('config')->set($key);
        }
        return app('config')->get($key, $default);
    }
}

if (! function_exists('database_path')) {
    /**
     * Get the database path.
     *
     * @param  string  $path
     * @return string
     */
    function database_path($path = '')
    {
        return app()->basePath() . '/cache/' . $path;
    }
}


if (! function_exists('logs')) {
    function logs(...$args)
    {
        $logger = app(LoggerInterface::class);

        if (! count($args)) {
            return $logger;
        }

        $message = '';
        $type = 'debug';
        $data = [];

        foreach ($args as $argument) {
            if (is_int($argument)) {
                $data[] = "Integer $argument";
            } elseif (is_bool($argument)) {
                $data[] = 'Boolean: ' . ($argument ? 'true' : 'false');
            } elseif (is_array($argument)) {
                $data = $argument;
            } elseif (is_object($argument)) {
                $data = (array) $argument;
            } elseif (in_array($argument, ['debug', 'info', 'error'])) {
                $type = $argument;
            } elseif (is_string($argument) || count($args) === 1) {
                $message = $argument;
            }
        }

        $logger->{$type}($message, $data);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->file($view, $data, $mergeData)->render();
    }
}
