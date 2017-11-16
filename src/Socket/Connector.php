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

namespace Discodian\Core\Socket;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Discodian\Core\Exceptions\MisconfigurationException;
use Discodian\Core\Socket\Requests\GatewayRequest;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Arr;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use RuntimeException;
use Ratchet\Client\Connector as WebsocketConnector;
use Discodian\Core\Events;

class Connector
{
    /**
     * @var string
     */
    protected $token;
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ClientInterface
     */
    protected $http;

    /**
     * @var Loop
     */
    protected $loop;

    /**
     * @var WebsocketConnector
     */
    protected $wsConnector;

    /**
     * @var WebSocket
     */
    protected $ws;

    /**
     * Connection state of websocket.
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * The package sequence.
     *
     * @var int
     */
    protected $sequence;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Heartbeat
     */
    protected $heartbeat;

    /**
     * @var Gateway URI.
     */
    protected $url;

    /**
     * @var int
     */
    protected $retries = -1;

    /**
     * @var string
     */
    protected $sessionId;
    /**
     * @var LoggerInterface
     */
    private $log;


    public function __construct(
        Repository $config,
        Application $app,
        ClientInterface $http,
        Dispatcher $events,
        LoopInterface $loop,
        LoggerInterface $log
    ) {
        $this->token = $config->get('discord.bot-token');
        $this->app = $app;
        $this->http = $http;
        $this->events = $events;
        $this->loop = $loop;
        $this->wsConnector = new WebsocketConnector($this->loop);
        $this->log = $log;
    }

    public function run()
    {
        if (!$this->app->runningInConsole()) {
            throw new RuntimeException('Bot can only run in PHP CLI.');
        }

        $this->log->debug('Starting Gateway request');

        $response = (new GatewayRequest())->request();

        $this->url = rtrim(Arr::get($response, 'url'), '/') . '/?' . http_build_query([
                'v' => config('discord.versions.gateway'),
                'encoding' => 'json'
            ]);
        // @todo
        $shards = Arr::get($response, 'shards');

        $this->log->debug("Gateway request returned url {$this->url} and shards {$shards}.");

        $this->connectWs();
    }

    protected function connectWs()
    {
        ++$this->retries;

        if ($this->retries > 5) {
            throw new RuntimeException('Too many retries.');
        }

        $this->log->debug("Setting up websocket connection after {$this->retries} retries.");

        $this->wsConnector->__invoke($this->url)->then(
            [$this, 'wsConnected'],
            [$this, 'wsError']
        );
    }

    public function wsConnected(WebSocket $socket)
    {
        $this->log->debug("Socket connected.");

        $this->ws = $socket;
        $this->heartbeat = $this->app->make(Heartbeat::class, [$this->loop]);
        $this->connected = true;
        $this->retries = -1;

        $socket->on('message', [$this, 'wsMessage']);
        $socket->on('close', [$this, 'wsClose']);
        $socket->on('error', [$this, 'wsError']);
    }

    public function wsClose(int $code, string $reason)
    {
        $this->log->alert("Closing connection <$code>: $reason");

        $this->connected = false;

        $this->events->dispatch(new Events\Ws\Close($code, $reason));

        $this->heartbeat()->cancel();

        if ($code === EventCode::CLOSE_INVALID_TOKEN) {
            throw new MisconfigurationException('Invalid token');
        }

        $this->connectWs();
    }

    public function wsError(\Exception $e)
    {
        $this->log->error("Error {$e->getMessage()}", $e->getTrace());

        $this->events->dispatch(new Events\Ws\Error($e));

        $this->wsClose(0, 'error');
    }

    public function wsMessage(Message $message)
    {
        $this->log->debug("Message {$message->count()}");
        $this->events->dispatch(new Events\Ws\Message($message));
    }

    public function heartbeat(): Heartbeat
    {
        return $this->heartbeat;
    }

    public function http(): ClientInterface
    {
        return $this->http;
    }

    public function ws(): WebSocket
    {
        return $this->ws;
    }

    public function send(array $data)
    {
        $this->ws()->send(json_encode($data));
    }

    public function sequence(int $sequence = null): int
    {
        if ($sequence !== null) {
            $this->sequence = $sequence;
        }

        return $this->sequence;
    }

    public function connected(): bool
    {
        return $this->connected;
    }

    public function identify(bool $resume = true)
    {
        $payload = [];
        Arr::set($payload, 'd.token', $this->token);

        if ($resume && $this->reconnecting && $this->sessionId) {
            Arr::set($payload, 'op', EventCode::RESUME);
            Arr::set($payload, 'd.seq', $this->sequence());
            Arr::set($payload, 'd.session_id', $this->sessionId);
        } else {
            Arr::set($payload, 'op', EventCode::IDENTIFY);
            Arr::set($payload, 'd.compress', true);
            Arr::set($payload, 'd.properties', [
                '$os'               => PHP_OS,
                '$browser'          => $this->http->getUserAgent(),
                '$device'           => $this->http->getUserAgent(),
                '$referrer'         => 'https://github.com/teamreflex/DiscordPHP',
                '$referring_domain' => 'https://github.com/teamreflex/DiscordPHP',
            ]);
        }
    }
}
