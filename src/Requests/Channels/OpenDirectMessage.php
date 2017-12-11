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
