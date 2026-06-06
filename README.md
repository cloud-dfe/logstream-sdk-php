# LogStream PHP SDK

PHP SDK for [LogStream](https://github.com/cloud-dfe/logstream-sdk) — a self-hosted log aggregator inspired by Papertrail.

Sends your application logs to your LogStream instance in real time. Works with any PHP project. No framework required.

## Requirements

- PHP 7.2+
- cURL extension enabled
- A running LogStream instance

## Installation

```bash
composer require logstream/laravel-sdk
```

## Standalone usage (any PHP project)

```php
use LogStream\LogStreamClient;

$log = new LogStreamClient(
    url: 'https://your-logstream.com',
    token: 'src_your_token_here',
    source: 'my-app',
    environment: 'production',
);

$log->info('User authenticated', ['user_id' => 42]);
$log->error('Payment failed', ['order_id' => 99]);
$log->warning('Rate limit reached');
$log->debug('Query executed', ['sql' => $query, 'time_ms' => 12]);
```

Available methods: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.

Or pass the level as a string:

```php
$log->log('error', 'Something went wrong', ['code' => 500]);
```

## Constructor options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$url` | string | — | Base URL of your LogStream instance |
| `$token` | string | — | Source token (starts with `src_`) |
| `$source` | string | `php-app` | Identifier for this application |
| `$environment` | string | `production` | Current environment name |
| `$hostname` | string\|null | auto-detected | Server hostname |
| `$timeout` | int | `3` | cURL timeout in seconds |

## PSR-3 usage

Install `psr/log` first:

```bash
composer require psr/log
```

Then wrap the client with `LogStreamLogger`:

```php
use LogStream\LogStreamClient;
use LogStream\Logger\LogStreamLogger;

$client = new LogStreamClient('https://your-logstream.com', 'src_token');
$logger = new LogStreamLogger($client);

// Now $logger implements Psr\Log\LoggerInterface
$logger->info('Hello from PSR-3', ['user_id' => 1]);
```

## Laravel integration

> Requires `psr/log`: `composer require psr/log`

### 1. Add the channel to `config/logging.php`

```php
'channels' => [
    'logstream' => [
        'driver' => 'custom',
        'via' => \LogStream\Laravel\LogStreamChannel::class,
        'url' => env('LOGSTREAM_URL'),
        'token' => env('LOGSTREAM_TOKEN'),
        'source' => env('LOGSTREAM_SOURCE', 'my-app'),
    ],

    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'logstream'],
    ],
],
```

### 2. Add to your `.env`

```env
LOG_CHANNEL=stack

LOGSTREAM_URL=https://your-logstream.com
LOGSTREAM_TOKEN=src_your_token_here
LOGSTREAM_SOURCE=my-app-name
```

### 3. Use normally

```php
Log::info('User authenticated', ['user_id' => 42]);
Log::error('Payment failed', ['order_id' => 99]);

// Or target the channel directly
Log::channel('logstream')->warning('Rate limit reached');
```

## Error handling

All errors during log delivery (network failures, timeouts, invalid responses) are silently swallowed. Logging should never crash your application.

## License

MIT
