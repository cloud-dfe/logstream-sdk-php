<?php

namespace LogStream;

class LogStreamClient
{
    private $url;
    private $token;
    private $source;
    private $environment;
    private $hostname;
    private $timeout;

    public function __construct(
        string $url,
        string $token,
        string $source = 'php-app',
        string $environment = 'production',
        ?string $hostname = null,
        int $timeout = 3
    ) {
        $this->url = rtrim($url, '/');
        $this->token = $token;
        $this->source = $source;
        $this->environment = $environment;
        $this->hostname = $hostname ?? (gethostname() ?: 'unknown');
        $this->timeout = $timeout;
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $microtime = microtime(true);
        $seconds = (int) $microtime;
        $microseconds = (int)(($microtime - $seconds) * 1000000);

        $this->send([
            'source' => $this->source,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'environment' => $this->environment,
            'hostname' => $this->hostname,
            'logged_at' => date('Y-m-d\TH:i:s', $seconds) . '.' . sprintf('%06d', $microseconds) . date('P'),
        ]);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    private function send(array $payload): void
    {
        $body = json_encode($payload);

        if ($body === false) {
            return;
        }

        try {
            $ch = curl_init("{$this->url}/api/ingest");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    "Authorization: Bearer {$this->token}",
                ],
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Silently fail — never let logging break the app
        }
    }
}
