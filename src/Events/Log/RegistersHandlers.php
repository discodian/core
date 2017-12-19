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

namespace Discodian\Core\Events\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class RegistersHandlers
{
    /**
     * @var array|HandlerInterface[]
     */
    public $handlers;
    /**
     * @var FormatterInterface
     */
    public $formatter;

    public function __construct(array &$handlers, FormatterInterface $formatter)
    {
        $this->handlers = &$handlers;
        $this->formatter = $formatter;
    }
}
