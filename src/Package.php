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

use Made\Blog\Engine\Package\PackageAbstract;
use Made\Blog\Engine\Service\ThemeLoadingService;
use Pimple\Container;
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
        $this->app = $app;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple): void
    {
        $this->register3rdPartyDependency($pimple);
    }

    /**
     * @param Container $container
     */
    private function register3rdPartyDependency(Container $container): void
    {
        $this->registerService($container, Twig::class, function (Container $container) {
            return (new ThemeLoadingService($container['engine']))
                ->loadTheme();
        });

        $this->app->add(TwigMiddleware::createFromContainer($this->app, Twig::class));
    }
}
