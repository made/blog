<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Made
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// The php built-in web-server's version of rewrites...
if (PHP_SAPI == 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $path = ltrim($url['path'], '/');

    if (!empty($path)) {
        $file = dirname(__DIR__) . '/public/' . $path;

        if (file_exists($file)) {
            return false;
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer(new Psr11Container($container));

/**
 * Instantiate App.
 *
 * In order for the factory to work you need to ensure you have installed a supported PSR-7 implementation of your
 * choice e.g.: Slim PSR-7 and a supported ServerRequest creator (included with Slim PSR-7).
 */
$app = AppFactory::create();

// Add Routing Middleware.
$app->addRoutingMiddleware();

/*
 * Add Error Handling Middleware.
 *
 * @param bool $displayErrorDetails -> Should be set to false in production.
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler.
 * @param bool $logErrorDetails -> Display error details in error log which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$container->register(new \Made\Blog\Engine\Package());
// TODO: Change namespace.
$container->register(new \App\Package());

// TODO: Move to another file if needed.
$app->get('/{slug:.*}', function (Request $request, Response $response, array $args) {
    /** @var string $slug */
    $slug = $args['slug'];

    $response->getBody()
        ->write("The slug is: '$slug'.");

    return $response;
});

$app->run();

return true;
