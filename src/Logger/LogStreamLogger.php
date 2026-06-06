<?php

namespace LogStream\Logger;

use LogStream\LogStreamClient;
use Psr\Log\AbstractLogger;

class LogStreamLogger extends AbstractLogger
{
    private $client;

    public function __construct(LogStreamClient $client)
    {
        $this->client = $client;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->client->log((string) $level, (string) $message, $context);
    }
}
