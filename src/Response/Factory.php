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

namespace Discodian\Core\Response;

use Discodian\Core\Requests\Channels\OpenDirectMessage;
use Discodian\Core\Requests\Channels\SendMessage;
use Discodian\Extend\Messages\Message;
use Discodian\Extend\Responses\Response;
use Discodian\Extend\Responses\TextResponse;
use Discodian\Parts\Channel\Channel;
use GuzzleHttp\ClientInterface;
use Discodian\Core\Parts\Factory as PartFactory;
use function GuzzleHttp\Promise\settle;

class Factory
{
    /**
     * @var ClientInterface
     */
    private $http;
    /**
     * @var PartFactory
     */
    private $parts;

    public function __construct(ClientInterface $http, PartFactory $parts)
    {
        $this->http = $http;
        $this->parts = $parts;
    }

    public function respond(Message $message, Response $response)
    {
        $channel = $message->channel;

        if ($response->private && !$message->channel->is_private) {
            $channel = $this->createPrivateChannel($message);
            logs($channel);
        }

        logs("Response factory ::respond channel", $channel->toArray());

        $promise = (new SendMessage($channel->id, $response))->request();

        return $promise->wait();
    }

    public function createPrivateChannel(Message $message): Channel
    {
        $promise = (new OpenDirectMessage($message->author))->request();

        $response = $promise->wait();

        return $this->parts->create(Channel::class, $response);
    }
}
