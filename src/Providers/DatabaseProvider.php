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

use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Support\ServiceProvider;

class DatabaseProvider extends ServiceProvider
{
    public function register()
    {
        if (config('database.default', false) !== false) {
            $this->app->register(DatabaseServiceProvider::class);
            $this->app->register(MigrationServiceProvider::class);


            $this->update();
        }
    }

    protected function update()
    {
        /** @var Migrator $migrator */
        $migrator = $this->app->make('migrator');

        $repository = $migrator->getRepository();

        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }

        $migrator->run(__DIR__ . '/../../migrations/');
    }
}
