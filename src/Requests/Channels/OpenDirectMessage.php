<?php

namespace Discodian\Core\Requests\Channels;

use Discodian\Core\Requests\Request;
use Discodian\Parts\User\User;

class OpenDirectMessage extends Request
{
    protected $path = 'users/@me/channels';
    protected $method = 'post';

    public function __construct(User $recipient)
    {
        $this->params = [
            'recipient_id' => $recipient->id
        ];
    }
}
