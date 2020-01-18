<?php

/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Made
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
 */

namespace App;

use App\Controller\BlogController;
use Made\Blog\Engine\Service\ThemeService;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

/**
 * Class Package
 */
class Package extends PackageAbstract
{
    /**
     * @var App
     */
    private $app;

    /**
     * Package constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct(null);

        $this->app = $app;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * Make sure the parent is called when overriding this function.
     *
     * @param Container $pimple A container instance
     * @throws PackageException
     */
    public function register(Container $pimple): void
    {
        parent::register($pimple);

        $this->registerService(App::class, function (Container $container) {
            return $this->app;
        });

        $this->register3rdPartyDependency();

        $this->registerController();
    }

    /**
     * @throws PackageException
     */
    private function register3rdPartyDependency(): void
    {
        $this->registerConfiguration(Twig::class, [
            // TODO: Complete option list with defaults.
            'cache' => false,
        ]);

        $this->registerConfiguration(Logger::class, [
            'name' => 'app',
            'filename' => dirname(__DIR__) . '/var/log/app.log',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Twig::class, function (Container $container) use ($configuration): Twig {
            /** @var array $settings */
            $settings = $configuration[Twig::class];

            /** @var ThemeService $themeService */
            $themeService = $container[ThemeService::class];

            $twig = Twig::create($themeService->getPathAndNamespace(), $settings);
            $themeService->updateLoader($twig->getLoader());

            return $twig;
        });

        $twigMiddleware = TwigMiddleware::createFromContainer($this->app, Twig::class);
        $this->app->add($twigMiddleware);

        $this->registerService(Logger::class, function (Container $container) use ($configuration): Logger {
            /** @var array $settings */
            $settings = $configuration[Logger::class];

            /** @var Logger $logger */
            $logger = new Logger($settings['name']);

            /** @var RotatingFileHandler $handler */
            $handler = new RotatingFileHandler($settings['filename']);
            $handler->setFilenameFormat('{date}_{filename}', 'Ymd');

            $logger->pushHandler($handler);

            return $logger;
        });

        $this->registerServiceAlias(LoggerInterface::class, Logger::class);
    }

    private function registerController(): void
    {
        $this->registerService(BlogController::class, function (Container $container): BlogController {
            /** @var Twig $twig */
            $twig = $container[Twig::class];
            /** @var Logger $logger */
            $logger = $container[Logger::class];

            return new BlogController($twig, $logger);
        });

        BlogController::register($this->app);
    }
}
