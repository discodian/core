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

use Discodian\Core\Cache\Listener;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Support\ServiceProvider;

class CacheProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(CacheServiceProvider::class);

        $this->app->make('events')->subscribe(Listener::class);
    }
}
