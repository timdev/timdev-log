<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Middleware;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use TimDev\Log\Logger;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;

class LoggerTest extends TestCase
{

    /** @psalm-suppress PropertyNotSetInConstructor */
    private TestHandler $handler;

    private function logger(): Logger
    {
        $logger = Logger::create('test', '/dev/null');
        $logger->getWrapped()->pushHandler($this->handler = new TestHandler());
        return $logger;
    }

    public function testThrowableWithOneArgument(): void
    {
        $this->logger()->exception(new \RuntimeException("Oh No."));
        assertSame("Oh No.", $this->handler->getRecords()[0]['message']);
    }

    public function testThrowableWithTwoArguments(): void
    {
        $this->logger()->exception(new \LogicException('Ya dun goofed!'), 'Try harder');
        assertSame('Try harder', $this->handler->getRecords()[0]['message']);
        $ctx = $this->handler->getRecords()[0]['context'];
        assertInstanceOf(\LogicException::class, $ctx['exception']);
        assertSame('Ya dun goofed!', $ctx['exception']->getMessage());
    }

    public function testEvent(): void
    {
        $this->logger()->event('thing-happened');
        $rec = $this->handler->getRecords()[0];
        assertSame('event:thing-happened', $rec['message']);
        assertSame('thing-happened', $rec['context']['evt']);
        assertSame('INFO', $rec['level_name']);
    }

    public function testEventWithData(): void
    {
        $this->logger()->event('thing-created', ['thing_id' => 1234]);
        $rec = $this->handler->getRecords()[0];
        assertSame('thing-created', $rec['context']['evt']);
        assertSame(1234, $rec['context']['thing_id']);
    }

    public function testEventWithDataAndCustomMessage(): void
    {
        $this->logger()->event(
            'thing-deleted',
            ['thing_id' => 1234, 'deleted_by' => 8882],
            'Bob deleted a thing'
        );

        $rec = $this->handler->getRecords()[0];
        assertSame('Bob deleted a thing', $rec['message']);
        assertSame('thing-deleted', $rec['context']['evt']);
        assertSame(8882, $rec['context']['deleted_by']);
    }

    public function testFactoryMethod(): void
    {
        $logger = Logger::create('log');
        assertSame('log', $logger->getWrapped()->getName());

        $logger = Logger::create('with-browser', '/dev/null', true);
        assertInstanceOf(StreamHandler::class, $logger->getWrapped()->getHandlers()[1]);
        assertInstanceOf(BrowserConsoleHandler::class, $logger->getWrapped()->popHandler());
    }
}
