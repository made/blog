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

namespace App;

use App\Controller\BlogController;
use Cache\Cache;
use Cache\Psr16\Cache as Psr16Cache;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Service\ThemeService;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;

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
     * @param Container $container
     * @param App $app
     */
    public function __construct(Container $container, App $app)
    {
        parent::__construct($container);

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

        $this->registerClientDependency();
        $this->registerPackageDependency();

        $this->registerController();
    }

    /**
     * @throws PackageException
     */
    private function registerClientDependency(): void
    {
        $this->registerConfiguration(Twig::class, [
            // TODO: Complete option list with defaults.
            'cache' => false,
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Twig::class, function (Container $container) use ($configuration): Twig {
            /** @var array $settings */
            $settings = $configuration[Twig::class];

            /** @var ThemeService $themeService */
            $themeService = $container[ThemeService::class];

            $twig = Twig::create($themeService->getPathAndNamespace(), $settings);
            $themeService->updateLoader($twig->getLoader());

            $twigEnvironment = $twig->getEnvironment();

            if ($twigEnvironment->isDebug()) {
                $twigEnvironment->addExtension(new DebugExtension());
            }

            return $twig;
        });

        // TODO: This pulls the twig class from the container which in turn tries to pull the theme service and so on.
//        $twigMiddleware = TwigMiddleware::createFromContainer($this->app, Twig::class);
//        $this->app->add($twigMiddleware);
    }

    /**
     * @throws PackageException
     */
    private function registerPackageDependency(): void
    {
        $this->registerConfiguration(Logger::class, [
            'name' => 'app',
            'filename' => dirname(__DIR__) . '/var/log/app.log',
        ]);

        $this->registerConfiguration(Cache::class, [
            'path' => dirname(__DIR__) . '/var/cache',
        ]);

        $this->registerConfiguration(Psr16Cache::class, [
            'cache_expiry_time' => strtotime('+1 Day', 0),
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

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

        // Alias the implementation.
        $this->registerServiceAlias(LoggerInterface::class, Logger::class);

        $this->registerService(Cache::class, function (Container $container) use ($configuration): Cache {
            /** @var array $settings */
            $settings = $configuration[Cache::class];

            return new Cache($settings['path']);
        });

        $this->registerService(Psr16Cache::class, function (Container $container) use ($configuration): Psr16Cache {
            /** @var array $settings */
            $settings = $configuration[Psr16Cache::class];

            /** @var Cache $cache */
            $cache = $container[Cache::class];

            return (new Psr16Cache($cache))
                ->setCacheExpiryTime($settings['cache_expiry_time']);
        });

        // Alias the implementation.
        $this->registerServiceAlias(CacheInterface::class, Psr16Cache::class);
    }

    private function registerController(): void
    {
        $this->registerService(BlogController::class, function (Container $container): BlogController {
            /** @var Twig $twig */
            $twig = $container[Twig::class];
            /** @var Logger $logger */
            $logger = $container[Logger::class];

            $repository = $container[PostConfigurationRepositoryInterface::class];

            return new BlogController($twig, $logger, $repository);
        });

        BlogController::register($this->app);
    }
}
