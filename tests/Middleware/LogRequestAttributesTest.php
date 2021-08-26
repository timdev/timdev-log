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

use TimDev\Test\Log\Support\TestSupport;

use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertSame;

class LogRequestAttributesTest extends TestCase
{

    use TestSupport;

    public function testSimpleValue(): void
    {
        // A Request with a string at RequestId and an object at user
        $logger = new Psr3StackLogger();
        $mw = $this->middleware($logger);

        $this->dispatch($mw, $this->request());

        $logger->debug('I should have three context elements');
        assertCount(3, $logger->contextAt(0));
        assertSame('AAAZZZ', $logger->contextAt(0)['request-id']);
    }

    private function middleware(StackLogger $logger): LogRequestAttributes
    {
        return new LogRequestAttributes(
            $logger,
            [
                // easy mapping for easy cases
                'request-id' => 'RequestId',

                // extract the id from the 'user' attribute, if it's present.
                'user-id' => function (ServerRequestInterface $req) {
                    /** @var DummyUser|null $user */
                    $user = $req->getAttribute('user');
                    return $user instanceof DummyUser
                        ? $user->getId()
                        : null;
                },

                // DummyUser => array{id: int, name: string}
                'user' => function (ServerRequestInterface $req) {
                    $user = $req->getAttribute('user');
                    // in this example, we *know* there should be a user there.
                    assert($user instanceof DummyUser);
                    return ['id' => $user->getId(), 'name' => $user->getName()];
                }
            ]
        );
    }

//    /**
//     * Dispatch $request through the provided $middleware(s) and return the response.
//     */
//    private function dispatch(array|MiddlewareInterface $middleware, ServerRequestInterface $request): ResponseInterface
//    {
//        if ($middleware instanceof MiddlewareInterface) {
//            $middleware = [$middleware];
//        }
//        // add a final response-factory middleware
//        $middleware[] = static fn(): ResponseInterface => new Response();
//        return (new Relay($middleware))->handle($request);
//    }
//
//    private function request(): ServerRequestInterface
//    {
//        return (new Psr17Factory())->createServerRequest('GET', '/')
//            ->withAttribute('RequestId', 'AAAZZZ')
//            ->withAttribute('user', new DummyUser());
//    }

    public function testSimpleValueViaCallable(): void
    {
        // A Request with a string at RequestId and an object at user
        $logger = new Psr3StackLogger();
        $mw = $this->middleware($logger);

        $this->dispatch($mw, $this->request());

        $logger->debug('I should have three context elements');
        assertCount(3, $logger->contextAt(0));
        assertSame(8675309, $logger->contextAt(0)['user-id']);
    }

    public function testArrayViaCallable(): void
    {
        // A Request with a string at RequestId and an object at user
        $logger = new Psr3StackLogger();
        $mw = $this->middleware($logger);

        $this->dispatch($mw, $this->request());

        $logger->debug('I should have three context elements');
        assertCount(3, $logger->contextAt(0));
        assertSame(['id' => 8675309, 'name' => 'Some Dummy'], $logger->contextAt(0)['user']);
    }

    public function testDoesNotAddNullValuedContextElements(): void
    {
        $logger = new Psr3StackLogger();
        $request = $this->request()->withAttribute('null-attr', null);
        $mw = new LogRequestAttributes($logger, ['null-target' => 'null-attr']);
        $this->dispatch($mw, $request);
        $logger->error('I should have empty context');
        assertArrayNotHasKey('null-target', $logger->contextAt(0));
        assertEmpty($logger->contextAt(0));
    }

    public function testIgnoresMissingRequestAttributes(): void
    {
        $logger = new Psr3StackLogger();
        $request = $this->request();
        // null-attr doesn't exist on the request!
        $mw = new LogRequestAttributes($logger, ['null-target' => 'null-attr']);
        $this->dispatch($mw, $request);
        $logger->error('I should have empty context');

        // but nothing bad happens.
        assertArrayNotHasKey('null-target', $logger->contextAt(0));
        assertEmpty($logger->contextAt(0));
    }
}
