<?php

namespace Discodian\Core\Requests\Channels;

use Discodian\Core\Requests\Request;
use Discodian\Extend\Responses\Response;
use Discodian\Extend\Responses\TextResponse;

class SendMessage extends Request
{
    protected $path = 'channels/:channel_id/messages';
    protected $method = 'post';

    public function __construct(string $channel_id, Response $response)
    {
        $this->properties['channel_id'] = $channel_id;

        $params = [];
        $params['content'] = $response->content;

        if ($response instanceof TextResponse) {
            $params['tts'] = $response->tts;
            $params['embed'] = $response->embed;
        }

        $this->params = $params;
    }
}
