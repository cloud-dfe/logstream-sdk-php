<?php

namespace LogStream\Laravel;

use LogStream\LogStreamClient;
use Monolog\Handler\AbstractProcessingHandler;

class LogStreamMonologHandler extends AbstractProcessingHandler
{
    private $client;

    // 100 = Logger::DEBUG em ambas as versões do Monolog (2 e 3)
    public function __construct(LogStreamClient $client, $level = 100, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
    }

    protected function write($record): void
    {
        if (is_array($record)) {
            // Monolog 2: $record é um array
            $level = strtolower($record['level_name']);
            $message = $record['message'];
            $context = isset($record['context']) ? $record['context'] : [];
        } else {
            // Monolog 3: $record é um objeto LogRecord
            $level = strtolower($record->level->name);
            $message = $record->message;
            $context = $record->context;
        }

        $this->client->log($level, $message, $this->normalizeContext($context));
    }

    private function normalizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if ($value instanceof \Throwable) {
                $context[$key] = [
                    'class' => get_class($value),
                    'message' => $value->getMessage(),
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'trace' => $value->getTraceAsString(),
                    'previous' => $value->getPrevious() ? $value->getPrevious()->getMessage() : null,
                ];
            }
        }

        return $context;
    }
}
