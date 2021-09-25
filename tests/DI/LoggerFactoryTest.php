<?php
declare(strict_types=1);

namespace TimDev\Test\Log\DI;

use PHPUnit\Framework\TestCase;

use Psr\Container\ContainerInterface;
use TimDev\Log\ConfigProvider;
use TimDev\Log\DI\LoggerFactory;

use function PHPUnit\Framework\assertSame;

class LoggerFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $defaultConfig = (new ConfigProvider())();
        $container->method('get')->willReturn($defaultConfig);

        $factory = new LoggerFactory();

        $logger = $factory->__invoke($container);
        assertSame('app', $logger->getWrapped()->getName());
    }

    public function testThrowsWhenConfigMissing(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn([
            'dependencies' => [],
            'timdev' => []          // <== We expect a 'log' sub-element here!
        ]);

        $this->expectException(\LogicException::class);
        $factory = new LoggerFactory();
        $logger = $factory->__invoke($container);

    }
}
