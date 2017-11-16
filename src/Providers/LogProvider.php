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

namespace Discodian\Core\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class LogProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoggerInterface::class, function (Application $app) {
            $handlers = [];

            if ($path = config('log.path')) {
                $handlers[] = new StreamHandler(
                    sprintf('%s%s.log', $path, date('Y-m-d'))
                );
            }

            if ($app->runningInConsole()) {
                $handlers[] = new StreamHandler($app->make(ConsoleOutputInterface::class)->getStream());
            }

            return new Logger('Discodian', $handlers);
        });
    }
}
