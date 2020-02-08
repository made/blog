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
     * ToDo: This test action should later be deleted!!!
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
//            $res = $this->configurationRepository->getAllByStatus('publish', 'draft');
//            $res = $this->configurationRepository->getAllByStatus(['publish', 'draft']);
//            $res = $this->configurationRepository->getAllByStatus('DRAFT');
            $res = $this->configurationRepository->getAll();
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
