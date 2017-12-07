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

use Discodian\Core\Events\Log\RegistersHandlers;
use Discodian\Core\Events\Log\RegistersLogger;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
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

            $formatter = new LineFormatter();
            $formatter->includeStacktraces();

            if ($app->runningInConsole()) {
                $handlers[] = $this->consoleHandler($app, $formatter);
            }

            if ($path = config('log.path')) {
                $handlers[] = $this->fileHandler($app, $path, $formatter);
            }

            $app['events']->dispatch(new RegistersHandlers($handlers));

            $logger = new Logger($app->userAgent(), $handlers);

            $app['events']->dispatch(new RegistersLogger($logger));

            return $logger;
        });

        $this->registerErrorHandler();
    }

    protected function registerErrorHandler()
    {
        $this->app->call([ErrorHandler::class, 'register']);
    }

    protected function fileHandler($app, string $path, $formatter): HandlerInterface
    {
        $handler = new StreamHandler(
            sprintf('%s%s.log', $path, date('Y-m-d')),
            $app->environment('production') ? Logger::INFO : Logger::ERROR
        );

        $handler->setFormatter($formatter);

        return $handler;
    }

    protected function consoleHandler($app, $formatter): HandlerInterface
    {
        $handler = new StreamHandler(
            $app->make(ConsoleOutputInterface::class)->getStream(),
            $app->environment('production') ? Logger::ERROR : Logger::DEBUG
        );

        $handler->setFormatter($formatter);

        return $handler;
    }
}
