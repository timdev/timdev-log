<?php

declare(strict_types=1);

namespace TimDev\Log\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TimDev\StackLogger\StackLogger;

/**
 * Include the time that has elapsed since the beginning of the request
 */
class RequestTimeElapsed implements MiddlewareInterface
{
    public function __construct(private StackLogger $logger, private string $contextKey = 'elapsed')
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        $context = [
            $this->contextKey => fn(): float => microtime(true) - $startTime
        ];
        $this->logger->addContext($context);

        return $handler->handle($request);
    }
}
