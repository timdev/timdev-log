<?php

declare(strict_types=1);

namespace TimDev\Log\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This middleware intercepts redirect (301/302 status w/ a Location header)
 * responses and replaces any body content with a simple HTML page with a
 * meta-refresh tag.
 *
 * It's intended for use during development when you're outputting logs to the
 * browser console via some mechanism that relies on the browser executing code
 * in the body (specifically Monolog's BrowserConsoleHandler, but should work
 * with any similar mechanism).
 *
 * The redirect is "neutered" by removing the Location header, leaving the
 * status code intact. The original value of the Location header is added back
 * in the X-Location header.
 */
class DevelopmentRedirect implements MiddlewareInterface
{
    private int $refreshDelaySeconds;

    public function __construct(int $refreshDelaySeconds = 0)
    {
        $this->refreshDelaySeconds = $refreshDelaySeconds;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $lh = $response->getHeader('Location');

        if (!empty($lh) && in_array($response->getStatusCode(), [301, 302])) {
            // Replace any response body with our client-side redirection document.
            $body = $response->getBody();
            $body->rewind();
            $body->write($this->render($lh[0]));
            $body->rewind();

            //
            $loc = $response->getHeader('Location')[0] ?? '';
            if ($loc !== '') {
                $response = $response->withHeader('X-Location', $loc);
            }

            // Craft a new response based on the original
            $response = $response
                // Remove Location header to keep the browser from redirecting.
                ->withoutHeader('Location')
                // change content-type to text/html
                ->withoutHeader('Content-Type')
                ->withHeader('Content-Type', 'text/html');
        }

        return $response;
    }

    private function render(string $uri): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <title>Redirecting...</title>
                <meta http-equiv="refresh" content="{$this->refreshDelaySeconds};url={$uri}">
                <style>
                .container {
                    min-height: 100vh; 
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    text-align: center;
                }
                </style>
            </head>
            <body>
                <div class="container">
                    <div>
                    <h3>Redirecting...</h3>
                    <p>You should be redirected automatically. But if that doesn't work, click below.</p>
                    <a href="{$uri}">{$uri}</a>
                    </div>
                </div>
            </body>
        </html>
        HTML;
    }
}
