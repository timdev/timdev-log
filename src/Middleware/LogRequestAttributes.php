<?php

declare(strict_types=1);

namespace TimDev\Log\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TimDev\StackLogger\StackLogger;

/**
 * PSR-15 Middleware that extracts attributes from a Request and adds them as
 * context to an instance of StackLogger
 */
class LogRequestAttributes implements MiddlewareInterface
{

    /**
     * @param StackLogger                    $logger
     * @param array<string, string|callable> $map
     */
    public function __construct(private StackLogger $logger, private array $map)
    {
    }

    /** @psalm-suppress MixedAssignment */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = [];
        foreach ($this->map as $contextName => $source) {
            $contextValue = null;
            if (is_callable($source)) {
                $contextValue = \Closure::fromCallable($source)($request);
            }
            if (is_string($source)) {
                $contextValue = $request->getAttribute($source);
            }

            if (null !== $contextValue) {
                $context[$contextName] = $contextValue;
            }
        }

        $this->logger->addContext($context);

        return $handler->handle($request);
    }
}
