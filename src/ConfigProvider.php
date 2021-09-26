<?php

declare(strict_types=1);

namespace TimDev\Log;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'timdev' => ['log' => $this->getConfig()]
        ];
    }

    private function getDependencies(): array
    {
        return [];
    }

    private function getConfig(): array
    {
        return [
            // passed to monolog's constructor.
            'name' => 'app',

            // Logs will be sent here.
            'logfile' => 'php://stdout',

            // Override to to true to turn on browser console logging.
            'enable_browser_console' => false,

            // Number of seconds to pause before redirecting when the
            // \TimDev\Log\Middleware\DevelopmentRedirect middleware is active.
            'dev_redir_delay_seconds' => 0
        ];
    }
}
