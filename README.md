# LogStream PHP SDK

PHP SDK for [LogStream](https://github.com/cloud-dfe/logstream-sdk-php) — a self-hosted log aggregator inspired by Papertrail.

Sends your application logs to your LogStream instance in real time. Works with any PHP project — no framework required.

## Requirements

- PHP 7.2+
- cURL extension enabled
- A running LogStream instance

## Installation

```bash
composer require logstream/laravel-sdk
```

---

## Standalone usage (any PHP project)

Zero dependencies. Works with pure PHP 7.2+.

```php
use LogStream\LogStreamClient;

$log = new LogStreamClient(
    'https://your-logstream.com',
    'src_your_token_here',
    'my-app',        // source
    'production'     // environment
);

$log->info('User authenticated', ['user_id' => 42]);
$log->warning('Rate limit reached', ['requests' => 480]);
$log->error('Payment failed', ['order_id' => 99]);
$log->debug('Query executed', ['time_ms' => 12]);
```

Available methods: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.

Or use the generic method:

```php
$log->log('error', 'Something went wrong', ['code' => 500]);
```

### Constructor parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `$url` | string | — | Base URL of your LogStream instance |
| `$token` | string | — | Source token (starts with `src_`) |
| `$source` | string | `php-app` | Identifier for this application |
| `$environment` | string | `production` | Current environment name |
| `$hostname` | string\|null | auto-detected | Server hostname |
| `$timeout` | int | `3` | cURL timeout in seconds |

---

## Laravel integration

Uses Laravel's `custom` driver with a Monolog handler internally. No extra packages needed — Monolog is already part of Laravel.

### 1. Add the channel to `config/logging.php`

```php
use LogStream\Laravel\LogStreamChannel;

'channels' => [

    'logstream' => [
        'driver' => 'custom',
        'via'    => LogStreamChannel::class,
        'url'    => env('LOGSTREAM_URL'),
        'token'  => env('LOGSTREAM_TOKEN'),
        'source' => env('LOGSTREAM_SOURCE', 'my-app'),
    ],

    // To log both locally and to LogStream at the same time:
    'stack' => [
        'driver'   => 'stack',
        'channels' => ['daily', 'logstream'],
    ],

],
```

### 2. Add to `.env`

```env
LOG_CHANNEL=stack

LOGSTREAM_URL=https://your-logstream.com
LOGSTREAM_TOKEN=src_your_token_here
LOGSTREAM_SOURCE=my-app-name
```

The token is generated in the LogStream dashboard under **Sources → Create Source**.

### 3. Use normally

All `Log::*` calls are forwarded to LogStream automatically:

```php
Log::info('User authenticated', ['user_id' => 42]);
Log::error('Payment failed', ['order_id' => 99]);

// Or send only to LogStream:
Log::channel('logstream')->warning('Rate limit reached');
```

---

## PSR-3 usage

For projects that require a `Psr\Log\LoggerInterface` (e.g. dependency injection containers, third-party libraries), install `psr/log` first:

```bash
composer require psr/log
```

Then wrap the client:

```php
use LogStream\LogStreamClient;
use LogStream\Logger\LogStreamLogger;

$client = new LogStreamClient('https://your-logstream.com', 'src_token', 'my-app');
$logger = new LogStreamLogger($client);

// $logger now implements Psr\Log\LoggerInterface
$logger->info('Hello', ['user_id' => 1]);
```

Useful when injecting the logger into a class that depends on the interface:

```php
class PaymentService
{
    public function __construct(private \Psr\Log\LoggerInterface $logger) {}

    public function process(int $orderId): void
    {
        $this->logger->info('Processing payment', ['order_id' => $orderId]);
    }
}

$service = new PaymentService(new LogStreamLogger($client));
```

---

## Error handling

All errors during log delivery (network failures, timeouts, invalid responses) are silently swallowed. Logging will never crash your application.

---

## License

MIT
