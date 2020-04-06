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
use Made\Blog\Theme\Basic\Controller\BlogController as BlogControllerBasic;
use Made\Blog\Engine\Exception\FailedOperationException;
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
    const ROUTE_ROOT = 'blog.root';
    const ROUTE_HOME = 'blog.home';
    const ROUTE_POST_LIST = 'blog.post.list';
    const ROUTE_POST = 'blog.post';
    const ROUTE_CATEGORY_LIST = 'blog.category.list';
    const ROUTE_CATEGORY = 'blog.category';
    const ROUTE_TAG_LIST = 'blog.tag.list';
    const ROUTE_TAG = 'blog.tag';
    const ROUTE_AUTHOR_LIST = 'blog.author.list';
    const ROUTE_AUTHOR = 'blog.author';
    const ROUTE_SEARCH = 'blog.search';

    /**
     * @inheritDoc
     */
    public static function register(App $app): void
    {
        $app->get('/', static::class . ':rootAction')
            ->setName(static::ROUTE_ROOT);

        $app->get('/{locale:[a-z]{2}}', static::class . ':homeAction')
            ->setName(static::ROUTE_HOME);

        $app->get('/{locale:[a-z]{2}}/feed', static::class . ':postListAction')
            ->setName(static::ROUTE_POST_LIST);

        $app->get('/{locale:[a-z]{2}}/category', static::class . ':categoryListAction')
            ->setName(static::ROUTE_CATEGORY_LIST);

        $app->get('/{locale:[a-z]{2}}/category/{id:[\w\-]+}', static::class . ':categoryAction')
            ->setName(static::ROUTE_CATEGORY);

        $app->get('/{locale:[a-z]{2}}/tag', static::class . ':tagListAction')
            ->setName(static::ROUTE_TAG_LIST);

        $app->get('/{locale:[a-z]{2}}/tag/{id:[\w\-]+}', static::class . ':tagAction')
            ->setName(static::ROUTE_TAG);

        $app->get('/{locale:[a-z]{2}}/author', static::class . ':authorListAction')
            ->setName(static::ROUTE_AUTHOR_LIST);

        $app->get('/{locale:[a-z]{2}}/author/{name:[\w\-]+}', static::class . ':authorAction')
            ->setName(static::ROUTE_AUTHOR);

        $app->get('/{locale:[a-z]{2}}/search', static::class . ':searchAction')
            ->setName(static::ROUTE_SEARCH);

        // This has to be last!
        $app->get('/{locale:[a-z]{2}}/{slug:.*}', static::class . ':postAction')
            ->setName(static::ROUTE_POST);
    }

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var BlogControllerBasic
     */
    private $controller;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BlogController constructor.
     * @param Twig $twig
     * @param BlogControllerBasic $controller
     * @param LoggerInterface $logger
     */
    public function __construct(Twig $twig, BlogControllerBasic $controller, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->controller = $controller;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function rootAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $this->controller
                ->rootAction();

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function homeAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];

            $data = $this->controller
                ->homeAction($locale);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postListAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];

            $data = $this->controller
                ->postListAction($locale);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var string $slug */
            $slug = $args['slug'];

            $data = $this->controller
                ->postAction($locale, $slug);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function categoryListAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var int $page */
            $page = $request->getQueryParams()['page'] ?? 0;

            $data = $this->controller
                ->categoryListAction($locale, $page);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function categoryAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var string $id */
            $id = $args['id'];

            $data = $this->controller
                ->categoryAction($locale, $id);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function tagListAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var int $page */
            $page = $request->getQueryParams()['page'] ?? 0;

            $data = $this->controller
                ->tagListAction($locale, $page);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function tagAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var string $id */
            $id = $args['id'];

            $data = $this->controller
                ->tagAction($locale, $id);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function authorListAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var int $page */
            $page = $request->getQueryParams()['page'] ?? 0;

            $data = $this->controller
                ->authorListAction($locale, $page);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function authorAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var string $name */
            $name = $args['name'];

            $data = $this->controller
                ->authorAction($locale, $name);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function searchAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            /** @var string $locale */
            $locale = $args['locale'];
            /** @var string $search */
            $search = $request->getQueryParams()['search'] ?? null;
            /** @var int $page */
            $page = $request->getQueryParams()['page'] ?? 0;

            $data = $this->controller
                ->searchAction($locale, $search, $page);

            return $this->handle($request, $response, $data);
        } catch (Throwable $throwable) {
            // Log everything that might fail.
            $this->logger->error('Error on request in "' . __METHOD__ . '": ' . $throwable->getMessage(), [
                'throwable', $throwable,
            ]);
        }

        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $data
     * @return ResponseInterface
     * @throws FailedOperationException
     */
    private function handle(ServerRequestInterface $request, ResponseInterface $response, ?array $data): ResponseInterface
    {
        if (null === $data) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        if (null !== ($redirect = $data[BlogControllerBasic::VARIABLE_REDIRECT] ?? null)) {
            // And redirect there permanently.
            return $response
                ->withHeader('Location', $redirect)
                ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY);
        }

        /** @var string|null $template */
        $template = $data[BlogControllerBasic::VARIABLE_TEMPLATE] ?? null;

        if (null === $template) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $data = $data[BlogControllerBasic::VARIABLE_DATA] ?? [];

        try {
            // Use the twig-view helper for that.
            return $this->twig->render($response, $template, $data);
        } catch (LoaderError | RuntimeError | SyntaxError $error) {
            // In case of an error, go all in.
            throw new FailedOperationException($error->getRawMessage());
        }
    }
}
