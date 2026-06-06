<?php

namespace LogStream\Laravel;

use LogStream\LogStreamClient;
use Monolog\Logger;

class LogStreamChannel
{
    public function __invoke(array $config): Logger
    {
        $environment = isset($config['environment'])
            ? $config['environment']
            : (function_exists('app') ? app()->environment() : 'production');

        $client = new LogStreamClient(
            isset($config['url']) ? $config['url'] : '',
            isset($config['token']) ? $config['token'] : '',
            isset($config['source']) ? $config['source'] : 'laravel',
            $environment,
            isset($config['hostname']) ? $config['hostname'] : null,
            isset($config['timeout']) ? (int) $config['timeout'] : 3
        );

        return new Logger('logstream', [new LogStreamMonologHandler($client)]);
    }
}
