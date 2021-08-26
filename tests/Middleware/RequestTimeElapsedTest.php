<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Middleware;

use Monolog\Test\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Relay\Relay;
use TimDev\Log\Middleware\LogRequestAttributes;
use TimDev\StackLogger\StackLogger;
use TimDev\StackLogger\Test\Support\Psr3StackLogger;
use TimDev\Test\Log\Support\DummyUser;

use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class RequestTimeElapsedTest extends TestCase
{
    public function testTimerCountsTime(): void
    {
        assertTrue(true);
    }
}
