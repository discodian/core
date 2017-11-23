<?php

namespace Discodian\Core\Events\Log;

use Psr\Log\LoggerInterface;

class RegistersLogger
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(LoggerInterface &$logger)
    {
        $this->logger = &$logger;
    }
}
