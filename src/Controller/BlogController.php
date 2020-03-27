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
use Help\Path;
use Help\Slug;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\SlugParserInterface;
use Made\Blog\Theme\Base\Controller\BaseController;
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
class BlogController extends BaseController implements ControllerInterface
{
    /**
     * @inheritDoc
     */
    public static function register(App $app): void
    {
        $routeMap = static::getRouteMap();

        foreach ($routeMap as $pattern => $route) {
            [
                BaseController::METHOD => $method,
                BaseController::ACTION => $action,
                BaseController::NAME => $name,
            ] = $route;

            $action = static::class . ':' . $action;

            if (!is_array($method)) {
                $method = [
                    $method,
                ];
            }

            $app->map($method, $pattern, $action)
                ->setName($name);
        }
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
     * @param PostRepositoryInterface $postRepository
     * @param SlugParserInterface $slugParser
     */
    public function __construct(Twig $twig, LoggerInterface $logger, PostRepositoryInterface $postRepository, SlugParserInterface $slugParser)
    {
        parent::__construct($twig, $logger);

        $this->twig = $twig;
        $this->logger = $logger;
        $this->postRepository = $postRepository;
        $this->slugParser = $slugParser;
    }

    /**
     * /{slug:.*}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        /** @var string $slug */
        $slug = $args['slug'];

        // This is a serious tripwire! It will not be important anymore, when an actual favicon.ico exists. But this is
        // a browser flaw...
        if ('favicon.ico' === $slug) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        // Extract all needed information from the slug.
        [
            SlugParserInterface::MATCH_LOCALE => $matchLocale,
            SlugParserInterface::MATCH_SLUG => $matchSlug,
        ] = $this->slugParser
            ->parse($slug);

        // And then forget about it.
        unset($slug);

        // If information is missing, stop right there.
        if (empty($matchLocale) || empty($matchSlug)) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        // Try to find the post.
        $post = $this->postRepository
            ->getOneBySlug($matchLocale, $matchSlug);

        // If it is not found, try to find it by slug-redirect.
        if (null === $post) {
            $post = $this->postRepository
                ->getOneBySlugRedirect($matchLocale, $matchSlug);

            // And if it has not been found by now, give up.
            if (null === $post) {
                return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
            }

            // Otherwise create the slug.
            $slug = $this->getPostSlug($post);

            // And redirect there permanently.
            return $response
                ->withHeader('Location', $slug)
                ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY);
        }

        // Else, just render the page as usual.
        $template = $this->getPostTemplate($post);

        try {
            // Use the twig-view helper for that.
            return $this->twig->render($response, $template, [
                'locale' => $matchLocale,
                'post' => $post,
            ]);
        } catch (LoaderError | RuntimeError | SyntaxError $error) {
            $this->logger->error('Error on twig render: ' . $error->getRawMessage(), [
                'error', $error,
            ]);

            // In case of an error, go all in.
            return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Post $post
     * @return string
     */
    private function getPostSlug(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);

        $locale = $configuration->getLocale();
        $slug = $configuration->getSlug();

        $slug = Path::join($locale, $slug);
        $slug = Slug::sanitize($slug);

        return $slug;
    }

    /**
     * @param Post $post
     * @return string
     */
    private function getPostTemplate(Post $post): string
    {
        $configuration = $post->getConfiguration()
            ->getLocale(PostConfiguration::LOCALE_KEY_CURRENT);

        return $configuration->getTemplate();
    }
}
