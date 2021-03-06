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

use Discodian\Core\Exceptions\MisconfigurationException;
use Discodian\Core\Requests\RateLimiter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HttpProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RateLimiter::class);

        $this->app->singleton(ClientInterface::class, function ($app) {
            $config = $app->make('config');

            return $this->setupClient($app, $config);
        });
    }

    /**
     * @param Application $app
     * @param Repository $config
     * @return Client
     */
    protected function setupClient(Application $app, Repository $config): Client
    {
        if (! $config->get('discord.bot-token')) {
            throw new MisconfigurationException('Bot token is required, check config/discord.php.');
        }

        if (! $config->get('discord.endpoints.http-api')) {
            throw new MisconfigurationException('Bot http api endpoint is required, check config/discord.php.');
        }

        if (! $config->get('discord.versions.http-api')) {
            throw new MisconfigurationException('Bot version of http api endpoint is required, check config/discord.php.');
        }

        $client = new Client([
            'base_uri' => $config->get('discord.endpoints.http-api'),
            'query' => [
                'v' => $config->get('discord.versions.http-api'),
                'encoding' => 'json'
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => $app->userAgent(),
                'Authorization' => 'Bot ' . $config->get('discord.bot-token')
            ]
        ]);

        return $client;
    }
}
