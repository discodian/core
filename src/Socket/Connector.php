<?php

namespace Discodian\Core\Socket;

use Amp\Loop;
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


    public function __construct(Repository $config, Application $app, ClientInterface $http, Dispatcher $events)
    {
        $this->token = $config->get('discord.bot-token');
        $this->app = $app;
        $this->http = $http;
        $this->events = $events;
        $this->loop = Loop::run([$this, 'run']);
        $this->wsConnector = new WebsocketConnector($this->loop);
    }

    public function run()
    {
        if (! $this->app->runningInConsole()) {
            throw new RuntimeException('Bot can only run in PHP CLI.');
        }

        $response = (new GatewayRequest())->request();

        $this->url = Arr::get($response, 'url');
        // @todo
        $shards = Arr::get($response, 'shards');

        $this->connectWs();
    }

    protected function connectWs()
    {
        dd($this->url, $this->wsConnector, $this->loop);
        ++$this->retries;

        if ($this->retries > 5) {
            throw new RuntimeException('Too many retries.');
        }

        $this->wsConnector->__invoke($this->url)->then(
            [$this, 'wsConnected'],
            [$this, 'wsError']
        );
    }

    public function wsConnected(WebSocket $socket)
    {
        $this->ws = $socket;

        $this->heartbeat = new Heartbeat;

        $this->connected = true;

        $this->retries = -1;

        $socket->on('message', [$this, 'wsMessage']);
        $socket->on('close', [$this, 'wsClose']);
        $socket->on('error', [$this, 'wsError']);
    }

    public function wsClose(int $code, string $reason)
    {
        $this->connected = false;

        $this->events->dispatch(new Events\Ws\Close($code, $reason));

        $this->heartbeat()->cancel();

        if ($code === EventCode::CLOSE_INVALID_TOKEN) {
            throw new MisconfigurationException('Invalid token');
        }

        $this->connectWs();
    }

    public function wsError($e)
    {
        $this->events->dispatch(new Events\Ws\Error($e));

        $this->wsClose(0, 'error');
    }

    public function wsMessage(Message $message)
    {
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
}
