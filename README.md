# timdev/log

> Opinionated, structured, logging for PHP.

* Build on top of [monolog] and [timdev/stack-logger].
* Logs are structured as [ndjson]

## Included Middleware

StackLogger opens up some nice possibilities. Particularly in middleware-based
web applications, it can be nice to add some persistent context to the logger 
instance early in the request, so it's included in all subsequent logging calls.

This library includes several StackLogger-aware PSR-15-compatible middleware
that I've used in web app projects.

### TimDev\Log\Middleware\LogRequestAttributes

Extracts request attributes from the PSR7 ServerRequest and adds them as context
to the logger.

Example:

```php
use Psr\Http\Message\ServerRequestInterface as SRI;
use TimDev\Log\Middleware\LogRequestAttributes;

$middleware = new LogRequestAttributes(
    // A StackLogger instance
    $logger,
    // map of context-key => request-attribute name | callable
    [
        // Extract 'user_id' attribute from request, and set the 'uid' 
        // context value on the logger.
        'uid' => 'user_id',
        
        // The above is a shortcut for:
        'uid2' => fn(SRI $req) => $req->getAttribute('user_id'),
        
        // If you want other data from the request, you can use the same pattern
        // to get it. For example:
        'ref' => fn(SRI $req) => $req->getHeader('Referer')[0] ?? null
    ];  
);
```

The middleware will not set context keys for `null` values. 

For more example usage, see the [tests](tests/Middleware/LogRequestAttributesTest.php)

### TimDev\Log\Middleware\DevelopmentRedirect

This middleware strips the `Location` header from redirect-responses and 
replaces the body with basic HTML document that includes a meta-refresh tag.

This is handy if you're using something like Monolog's [BrowserConsoleHandler]
that relies on emitting javascript for the browser to execute in order to push
log messages to the browser's console.

Example:

```php
use Psr\Http\Message\ServerRequestInterface as SRI;
use TimDev\Log\Middleware\DevelopmentRedirect;

// Seconds to delay before refreshing. Default is zero.
$delaySeconds = 2;
$middleware = new DevelopmentRedirect($delaySeconds);
```

This middleware should usually be added *early* in your pipeline, since it only
touches the response, and you want the response-mutation to happen last or 
nearly-last. In my projects, I typically do something like this:

```php
// ErrorHandler is the outermost middleware.
$app->pipe(ErrorHandler::class);
// If we're adding it, the DR middlware is the second outer-most.
if (getenv('APP_ENV') === 'development'){
    $app->pipe(new DevelopmentRedirect(1));
}
// ... all the rest of my middlewares.
```


[monolog]: https://github.com/Seldaek/monolog
[timdev/stack-logger]: https://git.timdev.com/tim/php-stack-logger
[ndjson]: http://ndjson.org/
[BrowserConsoleHandler](https://github.com/Seldaek/monolog/blob/82ab6a5f4f9ad081856eee4c9458efed5ecd7156/src/Monolog/Handler/BrowserConsoleHandler.php)
