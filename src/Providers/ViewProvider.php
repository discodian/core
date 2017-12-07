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

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider;

class ViewProvider extends ServiceProvider
{
    public function register()
    {
        config(['view.paths' => []]);
        config(['view.compiled' => base_path('cache')]);

        $this->app->register(ViewServiceProvider::class);
        $this->app->alias('view', Factory::class);
    }
}
