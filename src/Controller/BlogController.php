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
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Service\PageDataProvider\Implementation\Base\PageDataProvider;
use Made\Blog\Engine\Service\PageDataResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Views\Twig;
use Throwable;
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

    const VARIABLE_TEMPLATE = PageDataProvider::VARIABLE_TEMPLATE;
    const VARIABLE_REDIRECT = PageDataProvider::VARIABLE_REDIRECT;

    /**
     * @inheritDoc
     */
    public static function register(App $app): void
    {
        $app->get('/{slug:.*}', static::class . ':slugAction')
            ->setName(static::ROUTE_SLUG);
    }

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var PageDataResolverInterface
     */
    private $pageDataResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BlogController constructor.
     * @param Twig $twig
     * @param PageDataResolverInterface $pageDataResolver
     * @param LoggerInterface $logger
     */
    public function __construct(Twig $twig, PageDataResolverInterface $pageDataResolver, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->pageDataResolver = $pageDataResolver;
        $this->logger = $logger;
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

        try {
            $data = $this->pageDataResolver
                ->resolve($slug);

            if (null === $data) {
                return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
            }

            if (null !== ($slugRedirect = $data[static::VARIABLE_REDIRECT])) {
                // And redirect there permanently.
                return $response
                    ->withHeader('Location', $slugRedirect)
                    ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY);
            }

            $template = $data[static::VARIABLE_TEMPLATE] ?? null;

            if (null === $template) {
                return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
            }

            try {
                // Use the twig-view helper for that.
                return $this->twig->render($response, $template, $data);
            } catch (LoaderError | RuntimeError | SyntaxError $error) {
                // In case of an error, go all in.
                throw new FailedOperationException($error->getRawMessage());
            }
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request: ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }
}
