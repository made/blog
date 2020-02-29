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
use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
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
    const ROUTE_POST_CONFIG_TEST = 'blog.post_config_test';

    /**
     * @inheritDoc
     */
    public static function register(App $app): void
    {
        $app->get('/post_config_test/{locale}', BlogController::class . ':postConfigurationTestAction')
            ->setName(BlogController::ROUTE_POST_CONFIG_TEST);
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
     * @var PostConfigurationRepository
     */
    private $configurationRepository;

    /**
     * BlogController constructor.
     * @param Twig $twig
     * @param LoggerInterface $logger
     * @param PostConfigurationRepository $configurationRepository
     */
    public function __construct(Twig $twig, LoggerInterface $logger, PostConfigurationRepository $configurationRepository)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->configurationRepository = $configurationRepository;
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

        $line = "The slug is: '$slug'.";

        $response->getBody()
            ->write($line);

        // This is a serious tripwire!
        if ('favicon.ico' === $slug) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $this->logger->info($line);

        return $response;
    }


    /**
     * /post_config_test/{locale}
     *
     * ToDo: This test action should be deleted later!!!
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function postConfigurationTestAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        ini_set('xdebug.var_display_max_depth', '10');
        ini_set('xdebug.var_display_max_children', '256');
        ini_set('xdebug.var_display_max_data', '1024');
        try {

            // ToDo: This should automatically be set via slug (/en/*) or from the language fallback in the config
//            $this->configurationRepository->setLocale($args['locale']);

//            $res = $this->configurationRepository->getAllByPostDate(new \DateTime());
            $res = $this->configurationRepository->getAllByStatus('publish', 'draft');
//            $res = $this->configurationRepository->getAllByStatus(...['publish', 'draft']);
//            $res = $this->configurationRepository->getAllByStatus('DRAFT');
//            $res = $this->configurationRepository->getAll();
            echo "<pre>";
            var_dump($res);
            echo "</pre>";
        } catch (PostConfigurationException $exception) {
            echo "<pre><h1>Important Context</h1>";
            var_dump($exception->getContext());
            echo "</pre>";
            throw $exception;
        }

        $response->getBody()
            ->write('ok');

        return $response;
    }
}
