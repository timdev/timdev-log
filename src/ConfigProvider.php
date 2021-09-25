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
            'name' => 'app',
            'logfile' => 'php://output',
            'enable_browser_console' => false
        ];
    }
}
