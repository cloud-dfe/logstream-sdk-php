# LogStream Monolog Handler

Monolog handler for [LogStream](https://github.com/cloud-dfe/logstream-sdk) — a self-hosted log aggregator inspired by Papertrail.

Sends your Laravel application logs to your LogStream instance in real time.

## Requirements

- PHP 8.2+
- Laravel 10+
- A running LogStream instance

## Installation

```bash
composer require logstream/monolog-handler
```

## Configuration

### 1. Add the channel to `config/logging.php`

```php
'channels' => [
    'logstream' => [
        'driver'  => 'monolog',
        'handler' => \LogStream\Handler\LogStreamHandler::class,
        'with'    => [
            'url'    => env('LOGSTREAM_URL'),
            'token'  => env('LOGSTREAM_TOKEN'),
            'source' => env('LOGSTREAM_SOURCE', 'my-app'),
            'async'  => true,
        ],
    ],

    'stack' => [
        'driver'   => 'stack',
        'channels' => ['daily', 'logstream'],
    ],
],
```

### 2. Add to your `.env`

```env
LOG_CHANNEL=stack

LOGSTREAM_URL=https://your-logstream-domain.com
LOGSTREAM_TOKEN=src_your_token_here
LOGSTREAM_SOURCE=my-app-name
```

The token is generated in the LogStream dashboard under **Sources → Create Source**.

## Usage

Once configured, all `Log::*` calls are automatically forwarded to LogStream:

```php
Log::info('User authenticated', ['user_id' => 42]);
Log::error('Payment failed', ['order_id' => 99, 'error' => $e->getMessage()]);
Log::warning('Rate limit reached');
```

To send only to LogStream explicitly:

```php
Log::channel('logstream')->error('Critical failure', ['code' => 500]);
```

## Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `url` | string | — | Base URL of your LogStream instance |
| `token` | string | — | Source token (starts with `src_`) |
| `source` | string | `laravel` | Identifier for this application |
| `async` | bool | `true` | Send after response (non-blocking) |

## License

MIT
