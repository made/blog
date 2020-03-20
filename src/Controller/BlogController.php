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
     * @var SlugParserInterface
     */
    private $slugParser;

    /**
     * BlogController constructor.
     * @param Twig $twig
     * @param LoggerInterface $logger
     * @param SlugParserInterface $slugParser
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(Twig $twig, LoggerInterface $logger, SlugParserInterface $slugParser, PostRepositoryInterface $postRepository)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->slugParser = $slugParser;
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

        [
            SlugParserInterface::MATCH_LOCALE => $matchLocale,
            SlugParserInterface::MATCH_SLUG => $matchSlug,
        ] = $this->slugParser->parse($slug);

        unset($slug);

        if (empty($matchLocale) || empty($matchSlug)) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        // TODO: Add trailing slash middleware.
        //  http://www.slimframework.com/docs/v4/cookbook/route-patterns.html

        $post = $this->postRepository->getOneBySlug($matchLocale, $matchSlug);
        if (null === $post) {
            $post = $this->postRepository->getOneBySlugRedirect($matchLocale, $matchSlug);

            if (null !== $post) {
                $locale = $post->getConfiguration()->getLocale();
                $slug = $post->getConfiguration()->getSlug();

                $slug = Path::join($locale, $slug);
                $slug = Slug::sanitize($slug);

                return $response
                    ->withHeader('Location', $slug)
                    ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY);
            }
        }

        if (null === $post) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        // TODO: Test ability to return a post as json.
        //  http://www.slimframework.com/docs/v4/objects/response.html#returning-json

        // TODO: Get the full PostConfiguration object.
        $postConfiguration = null;

        try {
            return $this->twig->render($response, '@App/index.html.twig', [
                'postConfiguration' => $postConfiguration,
                'post' => $post,
            ]);
        } catch (LoaderError | RuntimeError | SyntaxError $error) {
            // TODO: Logging.

            return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
