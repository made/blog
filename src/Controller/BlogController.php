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
use Fig\Http\Message\StatusCodeInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\SlugParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Views\Twig;
use Twig\Error\Error;

/**
 * Class BlogController
 *
 * @package App\Controller
 */
class BlogController implements ControllerInterface
{
    const ROUTE_SLUG = 'blog.slug';
    const ROUTE_TEST_SLUG = 'blog.test.slug';

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
     * @var Twig
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * BlogController constructor.
     * @param Twig $twig
     * @param LoggerInterface $logger
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(Twig $twig, LoggerInterface $logger, PostRepositoryInterface $postRepository)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->postRepository = $postRepository;
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
        /** @var string $slug */
        $slug = $args['slug'];

        // This is a serious tripwire! It will not be important anymore, when an actual favicon.ico exists. But this is
        // a browser flaw...
        if ('favicon.ico' === $slug) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $sp = new SlugParser();
        [
            SlugParser::MATCH_LOCALE => $matchLocale,
            SlugParser::MATCH_SLUG => $matchSlug,
        ] = $sp->parse($slug);

        if (empty($matchLocale) || empty($matchSlug)) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $post = $this->postRepository->getOneBySlug($matchLocale, $matchSlug);

        // TODO: Error handling.
        return $this->twig->render($response, '@App/index.html.twig', [
            'post' => $post,
        ]);
    }
}
