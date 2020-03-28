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

namespace Made\Blog\Theme\Base\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

/**
 * Class IndexController
 *
 * Provide an route implementation for the base theme.
 * - /
 * - /post
 * - /category
 * - /category/{id}
 * - /tag
 * - /tag/{id}
 * - /{slug}
 *
 * @package Made\Blog\Theme\Base
 */
class BaseController
{
    const METHOD = 'method';
    const ACTION = 'action';
    const NAME = 'name';

    /**
     * @return array
     */
    public static function getRouteMap(): array
    {
        return [
            '/{locale:[a-zA-z]}/' => [
                static::METHOD => 'GET',
                static::ACTION => 'homeAction',
                static::NAME => 'base.home',
            ],

            '/{locale:[a-zA-z]}/post' => [
                static::METHOD => 'GET',
                static::ACTION => 'postOverviewAction',
                static::NAME => 'base.post',
            ],

            '/{locale:[a-zA-z]}/category' => [
                static::METHOD => 'GET',
                static::ACTION => 'categoryOverviewAction',
                static::NAME => 'base.category.overview',
            ],
            '/{locale:[a-zA-z]}/category/{id:[a-zA-Z0-9\-]}' => [
                static::METHOD => 'GET',
                static::ACTION => 'categoryAction',
                static::NAME => 'base.category',
            ],

            '/{locale:[a-zA-z]}/tag' => [
                static::METHOD => 'GET',
                static::ACTION => 'tagOverviewAction',
                static::NAME => 'base.tag.overview',
            ],
            '/{locale:[a-zA-z]}/tag/{id:[a-zA-Z0-9\-]}' => [
                static::METHOD => 'GET',
                static::ACTION => 'tagAction',
                static::NAME => 'base.tag',
            ],

            '/{slug:.*}' => [
                static::METHOD => 'GET',
                static::ACTION => 'postAction',
                static::NAME => 'post.slug',
            ],
        ];
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
     * BaseController constructor.
     * @param Twig $twig
     * @param LoggerInterface $logger
     */
    public function __construct(Twig $twig, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function homeAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: home.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postOverviewAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: postOverview.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function categoryOverviewAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: categoryOverview.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function categoryAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: category.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function tagOverviewAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: tagOverview.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function tagAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: tag.');

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()
            ->write('You arrived at a register action: post.');

        return $response;
    }
}
