<?php

declare(strict_types=1);

namespace TimDev\Log\DI;

use Psr\Container\ContainerInterface;
use TimDev\Log\Logger;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['timdev']['log'] ?? false;

        if (!$config) {
            throw new \LogicException(
                "Configuration missing. Did you forget to add the ConfigProvider to you ConfigAggregator?"
            );
        }

        return Logger::create($config['name'], $config['logfile'], $config['enable_browser_console']);
    }

}
