<?php

declare(strict_types=1);

namespace TimDev\Log\DI;

use Psr\Container\ContainerInterface;
use TimDev\Log\Logger;
use TimDev\TypedConfig\Config;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container): Logger
    {
        /** @var array<string,string|bool>|false $config */
        $config = $container->get('config')['timdev']['log'] ?? false;

        if (!$config) {
            throw new \LogicException(
                "Configuration missing. Did you forget to add the ConfigProvider to you ConfigAggregator?"
            );
        }

        $config = new Config($config);

        return Logger::create(
            $config->string('name'),
            $config->string('logfile'),
            $config->bool('enable_browser_console')
        );
    }
}
