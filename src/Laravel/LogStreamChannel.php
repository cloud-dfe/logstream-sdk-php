<?php

namespace LogStream\Laravel;

use LogStream\Logger\LogStreamLogger;
use LogStream\LogStreamClient;

class LogStreamChannel
{
    public function __invoke(array $config): LogStreamLogger
    {
        if (!class_exists('\Psr\Log\AbstractLogger')) {
            throw new \RuntimeException(
                'psr/log is required for the Laravel channel. Run: composer require psr/log'
            );
        }

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

        return new LogStreamLogger($client);
    }
}
