<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Made
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
 *
 */

namespace App\Controller;

use App\ControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Views\Twig;

/**
 * Class BlogController
 *
 * @package App\Controller
 */
class BlogController implements ControllerInterface
{
    const ROUTE_SLUG = 'blog.slug';


    /**
     * @inheritDoc
     */
    public static function register(App $app): void
    {
        $app->get('/{slug:.*}', BlogController::class . ':slugAction')
            ->setName(BlogController::ROUTE_SLUG);
    }

    /**
     * @var Twig
     */
    private $twig;

    /**
     * BlogController constructor.
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function slugAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var string $slug */
        $slug = $args['slug'];

        $response->getBody()
            ->write("The slug is: '$slug'.");

        return $response;
    }
}
