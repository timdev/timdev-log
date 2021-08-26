<?php

namespace TimDev\Log;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use TimDev\StackLogger\MonologStackLogger;

class Logger extends MonologStackLogger
{
    public static function create(string $name, string $logfile, bool $enableBrowserConsole = false): self
    {
        $monolog = new MonologLogger($name, [new StreamHandler($logfile)]);
        if ($enableBrowserConsole) {
        }
        return new self($monolog);
    }
}
