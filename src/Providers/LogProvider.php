<?php

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
                    sprintf('%s%s.log', $path , date('Y-m-d')),
                    Logger::INFO
                );
            }

            if ($app->runningInConsole()) {
                $handlers[] = new StreamHandler($app->make(ConsoleOutputInterface::class)->getStream());
            }

            return new Logger('Discodian', $handlers);
        });
    }
}
