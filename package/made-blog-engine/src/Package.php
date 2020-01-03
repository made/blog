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
 */

namespace Made\Blog\Engine;

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Package\PackageAbstract;
use Made\Blog\Engine\Service\ThemeService;
use Pimple\Container;

/**
 * Class Package
 *
 * @package Made\Blog\Engine
 */
class Package extends PackageAbstract
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     * @throws Exception\PackageException
     */
    public function register(Container $pimple): void
    {
        if (!$this->hasConfigurationSupport($pimple)) {
            $this->addConfigurationSupport($pimple);
        }

        $this->registerEngineConfiguration($pimple);

        $this->registerThemeUtility($pimple);
    }

    /**
     * @param Container $container
     * @throws Exception\PackageException
     */
    private function registerEngineConfiguration(Container $container): void
    {
        $this->registerConfiguration($container, Configuration::CONFIGURATION_NAME, [
            // TODO: Find a way to detect the correct root directory.
            Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY => null,
            Configuration::CONFIGURATION_NAME_THEME => 'theme-base',
        ]);

        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService($container, Configuration::class, function (Container $container) use ($configuration): Configuration {
            /** @var array $settings */
            $settings = $configuration[Configuration::CONFIGURATION_NAME];

            return (new Configuration())
                ->setRootDirectory($configuration[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY])
                ->setTheme($settings[Configuration::CONFIGURATION_NAME_THEME]);
        });
    }

    /**
     * @param Container $container
     */
    private function registerThemeUtility(Container $container): void
    {
        $this->registerService($container, ThemeService::class, function (Container $container): ThemeService {
            /** @var Configuration $engine */
            $engine = $container[Configuration::class];

            return new ThemeService($engine);
        });
    }
}
