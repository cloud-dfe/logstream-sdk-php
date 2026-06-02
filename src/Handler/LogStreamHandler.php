<?php

namespace LogStream\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class LogStreamHandler extends AbstractProcessingHandler
{
    private string $url;

    private string $token;

    private string $source;

    private bool $async;

    public function __construct(
        string $url,
        string $token,
        string $source = 'laravel',
        bool $async = true,
        int|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
        $this->url = rtrim($url, '/');
        $this->token = $token;
        $this->source = $source;
        $this->async = $async;
    }

    protected function write(LogRecord $record): void
    {
        $payload = [
            'source' => $this->source,
            'level' => strtolower($record->level->name),
            'message' => $record->message,
            'context' => $record->context,
            'channel' => $record->channel,
            'environment' => app()->environment(),
            'hostname' => gethostname(),
            'logged_at' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
        ];

        if ($this->async && app()->bound('queue')) {
            dispatch(function () use ($payload) {
                $this->send($payload);
            })->afterResponse();
        } else {
            $this->send($payload);
        }
    }

    private function send(array $payload): void
    {
        try {
            $ch = curl_init("{$this->url}/api/ingest");
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    "Authorization: Bearer {$this->token}",
                ],
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable) {
            // Silently fail — never let logging break the app
        }
    }
}
