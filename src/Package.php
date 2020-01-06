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

use Made\Blog\Engine\Service\ThemeService;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
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

        $this->register3rdPartyDependency($pimple);
    }

    /**
     * @param Container $container
     * @throws PackageException
     */
    private function register3rdPartyDependency(Container $container): void
    {
        // TODO: Use a constant for the service name.
        $this->registerConfiguration('twig', [
            // TODO: Complete option list with defaults.
            'cache' => false,
        ]);

        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        // TODO: This could be done inside a function in the abstract class called "registerConfigurationAlias" or something along that line.
        //  This makes the configuration available under the class name. Not yet sure if that practice should be continued or if normal strings should be used instead.
        $this->registerConfiguration(Twig::class, $configuration['twig']);
        //  Same goes with this, as the configuration array is not handled by reference.
        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Twig::class, function (Container $container) use ($configuration): Twig {
            /** @var array $settings */
            $settings = $configuration[Twig::class];

            /** @var ThemeService $themeService */
            $themeService = $container[ThemeService::class];

            $twig = Twig::create($themeService->getPath(), $settings);
            $themeService->updateLoader($twig->getLoader());

            return $twig;
        });

        $twigMiddleware = TwigMiddleware::createFromContainer($this->app, Twig::class);
        $this->app->add($twigMiddleware);

        // TODO: monolog!
    }
}
