<?php

/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Made
 * Written by GameplayJDK
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

namespace Made\Blog\Engine\Package;

use ArrayObject;
use Closure;
use Made\Blog\Engine\Exception\PackageException;
use Pimple\Container;

/**
 * Class PackageAbstract
 *
 * TODO: Move pimple package utility classes to package gameplayjdk/pimple-package-utility.
 *
 * @package Made\Blog\Engine\Package
 */
abstract class PackageAbstract implements PackageInterface
{
    const SERVICE_NAME_TAG = 'tag';
    const SERVICE_NAME_CONFIGURATION = 'configuration';

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public abstract function register(Container $pimple): void;

    /**
     * @param Container $container
     * @param string $serviceName
     * @param Closure $closure
     */
    protected function registerService(Container $container, string $serviceName, Closure $closure): void
    {
        $container[$serviceName] = $closure;
    }

    /**
     * @param Container $container
     * @param string $serviceName
     * @param string $alias
     */
    protected function registerAlias(Container $container, string $serviceName, string $alias): void
    {
        $this->registerService($container, $alias, function (Container $container) use ($serviceName) {
            return $container[$serviceName];
        });
    }

    /**
     * Initialization for tagging support (https://github.com/silexphp/Pimple/issues/205#issuecomment-230919514).
     *
     * @param Container $container
     */
    protected function addTagSupport(Container $container): void
    {
        $container[static::SERVICE_NAME_TAG] = new ArrayObject();
    }

    /**
     * @param Container $container
     * @deprecated
     */
    protected function removeTagSupport(Container $container): void
    {
        unset($container[static::SERVICE_NAME_TAG]);
    }

    /**
     * @param Container $container
     * @param bool $shouldThrow
     * @return bool
     * @throws PackageException
     */
    protected function hasTagSupport(Container $container, bool $shouldThrow = false): bool
    {
        $tagSupport = (isset($container[static::SERVICE_NAME_TAG]) && ($container[static::SERVICE_NAME_TAG] instanceof ArrayObject));

        if (!$tagSupport && $shouldThrow) {
            throw new PackageException('Container does not support tagging!');
        }

        return $tagSupport;
    }

    /**
     * @param Container $container
     * @param string $tag
     * @param string $serviceName
     */
    protected function registerTag(Container $container, string $tag, string $serviceName): void
    {
        $container[static::SERVICE_NAME_TAG][$tag][] = $serviceName;
    }

    /**
     * @param Container $container
     * @param string $tag
     * @param string $serviceName
     * @param Closure $closure
     * @throws PackageException
     */
    protected function registerServiceWithTag(Container $container, string $tag, string $serviceName, Closure $closure): void
    {
        if ($this->hasTagSupport($container, true)) {
            $this->registerTag($container, $tag, $serviceName);
        }

        $this->registerService($container, $serviceName, $closure);
    }

    /**
     * @param Container $container
     */
    protected function addConfigurationSupport(Container $container): void
    {
        $container[static::SERVICE_NAME_CONFIGURATION] = [];
    }

    /**
     * @param Container $container
     */
    protected function removeConfigurationSupport(Container $container): void
    {
        unset($container[static::SERVICE_NAME_CONFIGURATION]);
    }

    /**
     * @param Container $container
     * @param bool $shouldThrow
     * @return bool
     * @throws PackageException
     */
    protected function hasConfigurationSupport(Container $container, bool $shouldThrow = false): bool
    {
        $configurationSupport = (isset($container[static::SERVICE_NAME_CONFIGURATION])) && (is_array($container[static::SERVICE_NAME_CONFIGURATION]));

        if (!$configurationSupport && $shouldThrow) {
            throw new PackageException('Container does not support configuration!');
        }

        return $configurationSupport;
    }

    /**
     * __WARNING__: Make sure the custom configuration is registered inside the container _before_ the package is loaded!
     *
     * @param Container $container
     * @param string $serviceName
     * @param array $configuration
     * @throws PackageException
     */
    protected function registerConfiguration(Container $container, string $serviceName, array $configuration): void
    {
        if (!$this->hasConfigurationSupport($container, true)) {
            return;
        }

        $settings = [];

        if (isset($container[static::SERVICE_NAME_CONFIGURATION][$serviceName]) && is_array($container[static::SERVICE_NAME_CONFIGURATION][$serviceName])) {
            $settings = $container[static::SERVICE_NAME_CONFIGURATION][$serviceName];
        }

        $settings = array_replace_recursive($configuration, $settings);

        // Overload the configuration.

        /** @var array $configuration */
        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];
        $configuration[$serviceName] = $settings;
        $container[static::SERVICE_NAME_CONFIGURATION] = $configuration;
    }
}
