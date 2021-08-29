<?php

declare(strict_types=1);

namespace TimDev\Test\Log\Middleware;

use Monolog\Test\TestCase;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TimDev\Log\Middleware\DevelopmentRedirect;
use TimDev\Test\Log\Support\TestSupport;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;

class DevelopmentRedirectTest extends TestCase
{
    use TestSupport;

    public function testGeneratedBodyContainsMetaRefreshTag(): void
    {
        $response = $this->testResponse();
        assertStringContainsString(
            '<meta http-equiv="refresh" content="2;url=https://example.com">',
            $response->getBody()->getContents()
        );
    }

    private function testResponse(): ResponseInterface
    {
        $mw = new DevelopmentRedirect(2);
        $request = $this->request();
        return $this->dispatch(
            $mw,
            $request,
            static fn(): ResponseInterface => (new Response())
                ->withStatus(302)
                ->withHeader(
                    'Location',
                    'https://example.com'
                )
        );
    }

    public function testLocationHeaderIsMoved(): void
    {
        $response = $this->testResponse();
        assertEmpty($response->getHeader('Location'));

        $xLocation = $response->getHeader('X-Location');
        assertCount(1, $xLocation);
        assertSame('https://example.com', $xLocation[0]);
    }
}
