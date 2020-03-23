<?php
/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Controller;

use App\ControllerInterface;
use App\Service\SlugHandler;
use Fig\Http\Message\StatusCodeInterface;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Help\Slug;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\SlugParserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
        // This is the most generic pattern, thus its route has to be registered last.
        $app->get('/{slug:.*}', BlogController::class . ':slugAction')
            ->setName(BlogController::ROUTE_SLUG);
    }

    /**
     * @var SlugHandler
     */
    private $slugHandler;

    /**
     * BlogController constructor.
     * @param SlugHandler $slugHandler
     */
    public function __construct(SlugHandler $slugHandler)
    {
        $this->slugHandler = $slugHandler;
    }

    /**
     * /{slug:.*}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function slugAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->slugHandler
            ->handle($request, $response, $args);
    }
}
