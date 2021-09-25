<?php

namespace TimDev\Log;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use TimDev\StackLogger\MonologStackLogger;

/**
 *
 */
class Logger extends MonologStackLogger
{
    public static function create(
        string $name,
        string $logfile = 'php://output',
        bool $enableBrowserConsole = false
    ): self {
        // Get a monolog instance, which we'll configure below.
        $monolog = new MonologLogger($name);

        // Opinion #1: Primary output to a stream, which should be stdout by default.
        $handler = new StreamHandler($logfile, MonologLogger::DEBUG);

        // Opinion #2: Logs should be json-formatted events. We have opinions
        // about what that means, see the class comments for JsonFormtter.
        $handler->setFormatter(new JsonFormatter());

        // Add our opinionated handler to our instance.
        $monolog->pushHandler($handler);

        // In development, we like to send events to browser console too.
        if ($enableBrowserConsole) {
            $monolog->pushHandler(new BrowserConsoleHandler());
        }

        // Wrap with StackLogger and return!
        return new self($monolog);
    }

    /**
     * Log a named application event
     *
     * Useful for application events you want to use as metrics. Event-type log
     * message have an `evt` key containing the $eventName, for easy querying.
     *
     * $log->event('user-login', ['user_id' => 12]): will produce a log record
     * like:
     *
     * {
     *   "evt": "user-login",
     *   "msg": "event:user-login",
     *   "user_id": 55,
     *    ...
     * }
     *
     *
     * @param string  $eventName The name of the event
     * @param array   $data      Contextual event data
     * @param ?string $message   Optional message value for log record
     */
    public function event(string $eventName, array $data = [], string $message = null): void
    {
        $this->info(
            $message ?? "event:{$eventName}",
            array_merge($data, ["evt" => $eventName])
        );
    }

    /**
     * Log throwable $t at level ERROR
     *
     * @param \Throwable  $t       Any throwable
     * @param string|null $message The log message. Default: $t->getMessage()
     * @param array       $context Additional context data to add to the log record
     */
    public function exception(\Throwable $t, ?string $message = null, array $context = []): \Throwable
    {
        $this->error(
            $message ?? $t->getMessage(),
            array_merge($context, ['exception' => $t])
        );
        return $t;
    }

}
