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

use Discodian\Extend\Messages\Message;
use Discodian\Extend\Responses\Response;
use Discodian\Extend\Responses\TextResponse;
use GuzzleHttp\ClientInterface;
use JiraRestApi\Project\Project;

class Factory
{
    /**
     * @var ClientInterface
     */
    private $http;

    public function __construct(ClientInterface $http)
    {
        $this->http = $http;
    }

    public function respond(Message $message, Response $response)
    {
        $body = [];

        if ($response instanceof TextResponse) {
            if ($response->private && !$message->channel->is_private) {
                logs("Extension requests private response on public channel.");
                // @todo create dm channel
            } else {
                $path  = "channels/{$message->channel_id}/messages";
            }
            $body = [
                'content' => $response->content,
                'tts' => $response->tts,
                'embed' => $response->embed
            ];
        } else {
            logs("Unhandled response: " . get_class($response));
        }

        if (isset($path)) {
            // @todo async promises
            $this->http->request('post', $path, [
                'json' => $body
            ]);
        }
    }
}
