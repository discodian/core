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

use Discodian\Core\Factory\Repository;
use Discodian\Core\Requests\Listener;
use Illuminate\Support\ServiceProvider;
use Discodian\Parts\Contracts\Registry as Contract;
use Discodian\Parts\Registry;

class PartProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            Contract::class,
            Registry::class
        );
        $this->app->singleton(Repository::class);

        $this->app->make('events')->subscribe(Listener::class);
    }
}
