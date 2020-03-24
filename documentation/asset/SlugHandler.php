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

namespace App\Service;

use Fig\Http\Message\StatusCodeInterface;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Help\Slug;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\Mapper\PostMapper;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\SlugParserInterface;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SlugHandler
 *
 * Dependency:
 * - "willdurand/negotiation": "^2.3"
 *
 * There are several things still wrong about this class.
 *
 * 1. The api should ideally not be available at the same path as the web content. This has several reasons. First of
 * all, the api content should not accidentally be index by search engines. Another reason is, that the api should not
 * be based on changing routes, which possibly could be serving redirects.
 * 2. The whole logic is stuck in the {@link handle()} method.
 *
 * @deprecated Controller should be used instead! This class will be moved to the documentation later on.
 * @package App\Service
 */
class SlugHandler
{
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
     * @var PostMapper
     */
    private $postMapper;

    /**
     * @var SlugParserInterface
     */
    private $slugParser;

    /**
     * @var Negotiator
     */
    private $negotiator;

    /**
     * SlugHandler constructor.
     * @param Twig $twig
     * @param LoggerInterface $logger
     * @param PostRepositoryInterface $postRepository
     * @param PostMapper $postMapper
     * @param SlugParserInterface $slugParser
     * @param Negotiator $negotiator
     */
    public function __construct(Twig $twig, LoggerInterface $logger, PostRepositoryInterface $postRepository, PostMapper $postMapper, SlugParserInterface $slugParser, Negotiator $negotiator)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->postRepository = $postRepository;
        $this->postMapper = $postMapper;
        $this->slugParser = $slugParser;
        $this->negotiator = $negotiator;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args)
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
            $slug = $this->createSlugFromPost($post);

            // And redirect there permanently.
            return $response
                ->withHeader('Location', $slug)
                ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY);
        }

        // Now do some header negotiation.
        if ($request->hasHeader('Accept')) {
            $mediaTypeHeader = $request->getHeaderLine('Accept');
            $mediaType = $this->negotiateMediaType($mediaTypeHeader);

            // If json format is requested, provide it.
            if ('application/json' === $mediaType) {
                // Convert the model to a nested array.
                $postData = $this->postMapper
                    ->toData($post);

                // Encode it as json.
                $content = Json::encode($postData, true);

                // And write it to the body.
                $response->getBody()
                    ->write($content);

                // Then respond with the expected response media type.
                return $response
                    ->withHeader('Content-Type', 'application/json');
            }
        }

        // Else, just render the page as usual.
        try {
            // Use the twig-view helper for that.
            return $this->twig->render($response, '@App/index.html.twig', [
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
    private function createSlugFromPost(Post $post): string
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
     * @param string $mediaTypeHeader
     * @return string
     */
    private function negotiateMediaType(string $mediaTypeHeader): string
    {
        $mediaType = 'text/html';
        $mediaTypeAccept = $this->negotiator->getBest($mediaTypeHeader, [
            'text/html',
            'application/json',
        ]);

        if (null !== $mediaTypeAccept && $mediaTypeAccept instanceof Accept) {
            $mediaType = $mediaTypeAccept->getType();
        }

        return $mediaType;
    }

    // In the package class:
//    private function registerSlugHandler(): void
//    {
//        $this->registerService(Negotiator::class, function (Container $container): Negotiator {
//            return new Negotiator();
//        });
//
//        $this->registerService(SlugHandler::class, function (Container $container): SlugHandler {
//            /** @var Twig $twig */
//            $twig = $container[Twig::class];
//            /** @var Logger $logger */
//            $logger = $container[Logger::class];
//            /** @var PostRepositoryInterface $postRepository */
//            $postRepository = $container[PostRepositoryInterface::class];
//            /** @var PostMapper $postMapper */
//            $postMapper = $container[PostMapper::class];
//            /** @var SlugParserInterface $slugParser */
//            $slugParser = $container[SlugParserInterface::class];
//            /** @var Negotiator $negotiator */
//            $negotiator = $container[Negotiator::class];
//
//            return new SlugHandler($twig, $logger, $postRepository, $postMapper, $slugParser, $negotiator);
//        });
//    }
}
