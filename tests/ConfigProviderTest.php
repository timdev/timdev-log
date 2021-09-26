<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Middleware;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use TimDev\Log\ConfigProvider;
use TimDev\Log\Logger;
use TimDev\TypedConfig\Config;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertSame;

class ConfigProviderTest extends TestCase
{
    public function testReturnsExpectedConfigValues(): void
    {
        $config = (new ConfigProvider())();
        assertIsArray($config['dependencies']);
        assertIsArray($config['timdev']);
        assertIsArray($config['timdev']['log']);
    }
}
