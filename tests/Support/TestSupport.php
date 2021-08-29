<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Relay\Relay;

trait TestSupport
{
    /**
     * Dispatch $request through the provided $middleware(s) and return the response.
     */
    private function dispatch(array|MiddlewareInterface $middleware, ServerRequestInterface $request, ?callable $responseFactory = null): ResponseInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            $middleware = [$middleware];
        }
        // add terminal middleware that actually creates a response.
        $middleware[] = $responseFactory ?? static fn(): ResponseInterface => new Response();
        return (new Relay($middleware))->handle($request);
    }

    /**
     * Returns a simple ServerRequest with some attributes.
     */
    private function request(): ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute('RequestId', 'AAAZZZ')
            ->withAttribute('user', new DummyUser());
    }
}
