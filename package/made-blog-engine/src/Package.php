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

use Cache\Cache;
use Cache\Psr16\Cache as Psr16Cache;
use Made\Blog\Engine\Exception\ConfigurationException;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Package\TagResolverTrait;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\Configuration\ConfigurationService;
use Made\Blog\Engine\Service\Configuration\Strategy\ConfigurationStrategyInterface;
use Made\Blog\Engine\Service\Configuration\Strategy\File\FileConfigurationStrategy;
use Made\Blog\Engine\Service\ThemeService;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Package
 *
 * @package Made\Blog\Engine
 */
class Package extends PackageAbstract
{
    use TagResolverTrait;

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

        if (!$this->hasTagSupport(false)) {
            $this->addTagSupport();
        }

        if (!$this->hasConfigurationSupport(false)) {
            $this->addConfigurationSupport();
        }

        $this->registerConfigurationStuff($pimple);

        $this->registerCacheStuff($pimple);

        $this->registerDataLayer($pimple);

        $this->registerThemeService($pimple);
    }

    /**
     * @param Container $container
     * @throws PackageException
     */
    private function registerConfigurationStuff(Container $container): void
    {
        $this->registerTagAndService(ConfigurationStrategyInterface::TAG_CONFIGURATION_STRATEGY, FileConfigurationStrategy::class, function (Container $container): ConfigurationStrategyInterface {
            return new FileConfigurationStrategy();
        });

        // TODO: Currently this is set statically to use the file configuration strategy. The extra tagging is needed so the resolver can find the alias.
        $this->registerServiceAlias(ConfigurationStrategyInterface::class, FileConfigurationStrategy::class);
        $this->registerTag(ConfigurationStrategyInterface::TAG_CONFIGURATION_STRATEGY, ConfigurationStrategyInterface::class);

        $this->registerService(ConfigurationService::class, function (Container $container): ConfigurationService {
            /** @var array|ConfigurationStrategyInterface[] $configurationStrategyArray */
            $configurationStrategyArray = $this->resolveTag(ConfigurationStrategyInterface::TAG_CONFIGURATION_STRATEGY, ConfigurationStrategyInterface::class);
            /** @var ConfigurationStrategyInterface $configurationStrategy */
            $configurationStrategy = $configurationStrategyArray[ConfigurationStrategyInterface::class];

            return new ConfigurationService($configurationStrategy);
        });

        // Initialize the configuration.
        $this->initializeConfiguration($container);

        $this->registerConfiguration(Configuration::CONFIGURATION_NAME, [
            // TODO: Find a way to detect the correct root directory.
            Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY => null,
            Configuration::CONFIGURATION_NAME_THEME => 'theme-base',
        ]);

        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Configuration::class, function (Container $container) use ($configuration): Configuration {
            /** @var array $settings */
            $settings = $configuration[Configuration::CONFIGURATION_NAME];

            return (new Configuration())
                // The root directory is expected to be at the top level of the configuration array and has to be placed
                // there explicitly by the used configuration strategy.
                ->setRootDirectory($configuration[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY])
                ->setTheme($settings[Configuration::CONFIGURATION_NAME_THEME]);
        });
    }

    /**
     * Initialize the configuration. As of now, this method will replace all existing configuration with the newly
     * initialized one.
     *
     * TODO: Make this method use an array_merge() or something.
     *
     * @param Container $container
     * @throws PackageException
     */
    private function initializeConfiguration(Container $container): void
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = $container[ConfigurationService::class];

        try {
            $container[static::SERVICE_NAME_CONFIGURATION] = $configurationService->getConfigurationArray(true);
        } catch (ConfigurationException $ex) {
            throw new PackageException('Configuration exception.');
        }
    }

    /**
     * @param Container $container
     * @throws PackageException
     */
    private function registerCacheStuff(Container $container): void
    {
        // TODO: Rather use a constant for some stuff inside this function. Not sure where to place them, thought.

        $this->registerConfiguration('cache', [
            // TODO
            'path' => null,
        ]);

        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        // TODO: This could be done inside a function in the abstract class called "registerConfigurationAlias" or something along that line.
        //  This makes the configuration available under the class name. Not yet sure if that practice should be continued or if normal strings should be used instead.
        $this->registerConfiguration(Cache::class, $configuration['cache']);
        //  Same goes with this, as the configuration array is not handled by reference.
        $configuration = $container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Cache::class, function (Container $container) use ($configuration): Cache {
            /** @var array $settings */
            $settings = $configuration[Cache::class];

            // TODO: Make the path relative to the root directory.
            $path = $settings['path'];

            return new Cache($path);
        });

        $this->registerService(Psr16Cache::class, function (Container $container): Psr16Cache {
            /** @var Cache $cache */
            $cache = $container[Cache::class];

            return new Psr16Cache($cache);
        });

        // Alias the implementation.
        $this->registerServiceAlias(CacheInterface::class, Psr16Cache::class);
    }

    /**
     * @param Container $container
     * @throws PackageException
     */
    private function registerDataLayer(Container $container): void
    {
        // First register mapper.
        $this->registerService(ThemeMapper::class, function (Container $container): ThemeMapper {
            return new ThemeMapper();
        });

        // Then repository.
        $this->registerTagAndService(ThemeRepositoryInterface::TAG_THEME_REPOSITORY, ThemeRepository::class, function (Container $container): ThemeRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ThemeMapper $themeMapper */
            $themeMapper = $container[ThemeMapper::class];

            return new ThemeRepository($configuration, $themeMapper);
        });

        // Then alias the implementation.
        $this->registerServiceAlias(ThemeRepositoryInterface::class, ThemeRepository::class);
        $this->registerTag(ThemeRepositoryInterface::TAG_THEME_REPOSITORY, ThemeRepositoryInterface::class);

        // Then proxy.
        $container->extend(ThemeRepositoryInterface::class, function (ThemeRepositoryInterface $themeRepository, Container $container): ThemeRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyThemeRepository($cache, $themeRepository);
        });
    }

    /**
     * @param Container $container
     */
    private function registerThemeService(Container $container): void
    {
        $this->registerService(ThemeService::class, function (Container $container): ThemeService {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ThemeRepositoryInterface $themeRepository */
            $themeRepository = $container[ThemeRepositoryInterface::class];

            return new ThemeService($configuration, $themeRepository);
        });
    }
}
