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
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Package\TagResolverTrait;
use Made\Blog\Engine\Repository\ContentRepositoryInterface;
use Made\Blog\Engine\Repository\Implementation\Aggregation\ContentRepository as ContentRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\File\ContentRepository as ContentRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository;
use Made\Blog\Engine\Repository\Mapper\ContentMapper;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\Configuration\ConfigurationService;
use Made\Blog\Engine\Service\Configuration\Strategy\ConfigurationStrategyInterface;
use Made\Blog\Engine\Service\Configuration\Strategy\File\FileConfigurationStrategy;
use Made\Blog\Engine\Service\ContentService;
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

        $this->registerConfigurationStuff();

        $this->registerCacheStuff();

        $this->registerDataLayerTheme();
        $this->registerDataLayerContent();

        $this->registerThemeService();
        $this->registerContentService();
    }

    /**
     * @throws PackageException
     */
    private function registerConfigurationStuff(): void
    {
        $this->registerConfiguration(Configuration::class, [
            Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY => dirname(__DIR__, 4),
            Configuration::CONFIGURATION_NAME_THEME => 'theme-base',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Configuration::class, function (Container $container) use ($configuration): Configuration {
            /** @var array $settings */
            $settings = $configuration[Configuration::class];

            return (new Configuration())
                ->setRootDirectory($settings[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY])
                ->setTheme($settings[Configuration::CONFIGURATION_NAME_THEME]);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerCacheStuff(): void
    {
        // TODO: Rather use a constant for some stuff inside this function. Not sure where to place them, thought.

        $this->registerConfiguration(Cache::class, [
            'path' => dirname(__DIR__, 4) . '/var/cache',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

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
     * @throws PackageException
     */
    private function registerDataLayerTheme(): void
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

        // Then proxy.
        $this->container->extend(ThemeRepositoryInterface::class, function (ThemeRepositoryInterface $themeRepository, Container $container): ThemeRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyThemeRepository($cache, $themeRepository);
        });

    }

    /**
     * @throws PackageException
     */
    private function registerDataLayerContent(): void
    {
        // First register mapper.
        $this->registerService(ContentMapper::class, function (Container $container): ContentMapper {
            return new ContentMapper();
        });

        // Then repository.
        $this->registerTagAndService(ContentRepositoryInterface::TAG_CONTENT_REPOSITORY, ContentRepositoryFile::class, function (Container $container): ContentRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ContentMapper $contentMapper */
            $contentMapper = $container[ContentMapper::class];

            return new ContentRepositoryFile($configuration, $contentMapper);
        });

        // Then alias the implementation.
        $this->registerServiceAlias(ContentRepositoryInterface::class, ContentRepositoryFile::class);

        // Register the Aggregation ContentRepository
        $this->registerTagAndService(ContentRepositoryInterface::TAG_CONTENT_REPOSITORY, ContentRepositoryAggregation::class, function (Container $container): ContentRepositoryInterface {
            $classList = $this->resolveTag(ContentRepositoryInterface::TAG_CONTENT_REPOSITORY, ContentRepositoryInterface::class, [ContentRepositoryAggregation::class]);

            return new ContentRepositoryAggregation($classList);
        });
    }

    private function registerThemeService(): void
    {
        $this->registerService(ThemeService::class, function (Container $container): ThemeService {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ThemeRepositoryInterface $themeRepository */
            $themeRepository = $container[ThemeRepositoryInterface::class];

            return new ThemeService($configuration, $themeRepository);
        });
    }

    private function registerContentService(): void
    {
        $this->registerService(ContentService::class, function (Container $container): ContentService {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ContentRepositoryInterface $contentRepository */
            $contentRepository = $container[ContentRepositoryInterface::class];

            return new ContentService($configuration, $contentRepository);
        });
    }
}
