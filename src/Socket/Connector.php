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

use Discodian\Core\Events;
use Discodian\Core\Exceptions\MisconfigurationException;
use Discodian\Core\Socket\Requests\GatewayRequest;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Ratchet\Client\Connector as WebsocketConnector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\LoopInterface;
use function React\Promise\resolve;
use RuntimeException;

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
     * @var LoopInterface
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
     * @var bool
     */
    protected $reconnecting = false;

    /**
     * The package sequence.
     *
     * @var int
     */
    protected $sequence = 0;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var Heartbeat
     */
    protected $heartbeat;

    /**
     * Gateway URL.
     *
     * @var string
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
     * Connector constructor.
     * @param Repository $config
     * @param Application $app
     * @param ClientInterface $http
     * @param Dispatcher $events
     * @param LoopInterface $loop
     */
    public function __construct(
        Repository $config,
        Application $app,
        ClientInterface $http,
        Dispatcher $events,
        LoopInterface $loop
    )
    {
        $this->token = $config->get('discord.bot-token');
        $this->app = $app;
        $this->http = $http;
        $this->events = $events;
        $this->loop = $loop;
        $this->wsConnector = new WebsocketConnector($this->loop);
    }

    public function run()
    {
        if (!$this->app->runningInConsole()) {
            throw new RuntimeException('Bot can only run in PHP CLI.');
        }

        logs('Starting Gateway request');

        $request = (new GatewayRequest())->request();
        
        $request->then(function ($response) {
            dd($response, "RESPONSE");
                $this->url = rtrim(Arr::get($response, 'url'), '/') . '/?' . http_build_query([
                        'v' => config('discord.versions.gateway'),
                        'encoding' => 'json'
                    ]);
                // @todo sharding implementation.
                $shards = Arr::get($response, 'shards');

                logs("Gateway request returned url {$this->url} and shards {$shards}.");

                $this->connectWs();
            });

        resolve($request);
    }

    protected function connectWs()
    {
        ++$this->retries;

        if ($this->retries > 5) {
            throw new RuntimeException('Too many retries.');
        }

        logs("Setting up websocket connection after {$this->retries} retries.");

        $this->wsConnector->__invoke($this->url)->then(
            [$this, 'wsConnected'],
            [$this, 'wsError']
        );
    }

    public function wsConnected(WebSocket $socket)
    {
        logs("Socket connected.");

        $this->ws = $socket;
        $this->heartbeat = new Heartbeat($this->loop, $this);
        $this->connected = true;
        $this->retries = -1;

        Events\Event::setConnector($this);

        $socket->on('message', [$this, 'wsMessage']);
        $socket->on('close', [$this, 'wsClose']);
        $socket->on('error', [$this, 'wsError']);
    }

    public function wsClose(int $code, string $reason)
    {
        logs("Closing connection <$code>: $reason");

        $this->connected = false;

        $this->events->dispatch(new Events\Ws\Close($code, $reason));

        $this->heartbeat()->cancel();

        if ($code === Op::CLOSE_INVALID_TOKEN) {
            throw new MisconfigurationException('Invalid token');
        }

        $this->reconnecting = true;

        $this->connectWs();
    }

    public function wsError(\Exception $e)
    {
        logs('error', "Error {$e->getMessage()}", $e->getTrace());

        $this->events->dispatch(new Events\Ws\Error($e));

        $this->wsClose(0, 'error');
    }

    public function wsMessage(Message $message)
    {
        logs("Raw message received {$message->count()}");

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
            logs("Sequence updated {$this->sequence} => {$sequence}");

            $this->sequence = $sequence;
        }

        return $this->sequence;
    }

    public function connected(): bool
    {
        return $this->connected;
    }

    /**
     * @param bool $resume
     * @return bool Whether we're resuming.
     */
    public function identify(bool $resume = true): bool
    {
        $payload = [];

        Arr::set($payload, 'd.token', $this->token);

        if ($resume && $this->reconnecting && $this->sessionId) {
            Arr::set($payload, 'op', Op::RESUME);
            Arr::set($payload, 'd.seq', $this->sequence());
            Arr::set($payload, 'd.session_id', $this->sessionId);
        } else {
            Arr::set($payload, 'op', Op::IDENTIFY);
            Arr::set($payload, 'd.compress', true);
            Arr::set($payload, 'd.properties', [
                '$os' => PHP_OS,
                '$browser' => $this->app->userAgent(),
                '$device' => $this->app->userAgent(),
                '$referrer' => 'http://discodian.com',
                '$referring_domain' => 'http://discodian.com',
            ]);
        }

        logs('Identifying', $payload);

        $this->send($payload);

        logs('Identified.', ['resume' => $payload['op'] === Op::RESUME]);

        return $payload['op'] === Op::RESUME;
    }

    public function __destruct()
    {
//        $this->wsClose(Op::CLOSE_ABNORMAL, 'Terminated.');
    }
}
