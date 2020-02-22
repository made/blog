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
use Made\Blog\Engine\Package\TagResolverTrait;
use Made\Blog\Engine\Repository\Implementation\Aggregation\PostConfigurationRepository as PostConfigurationRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaCustomMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaMapper;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\PostConfigurationService;
use Made\Blog\Engine\Service\ThemeService;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\Log\LoggerInterface;
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

        if (!$this->checkPackagePrerequisite(true)) {
            return;
        }

        $this->registerConfigurationObject();

        $this->registerDataLayerTheme();
        $this->registerDataLayerPostConfiguration();

        $this->registerThemeService();
        $this->registerContentService();
    }

    /**
     * @param bool $shouldThrow
     * @return bool
     * @throws PackageException
     */
    private function checkPackagePrerequisite(bool $shouldThrow = false): bool
    {
        $packagePrerequisite = (isset($this->container[LoggerInterface::class]) && ($this->container[LoggerInterface::class] instanceof LoggerInterface))
            && (isset($this->container[CacheInterface::class]) && ($this->container[CacheInterface::class] instanceof CacheInterface));

        if (!$packagePrerequisite && $shouldThrow) {
            throw new PackageException('Container does not have package prerequsite!');
        }

        return $packagePrerequisite;
    }

    /**
     * @throws PackageException
     */
    private function registerConfigurationObject(): void
    {
        $this->registerConfiguration(Configuration::class, [
            Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY => dirname(__DIR__, 4),
            Configuration::CONFIGURATION_NAME_THEME => 'theme-base',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Configuration::class,
            function (Container $container) use ($configuration): Configuration {
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
    private function registerDataLayerTheme(): void
    {
        // First register mapper.
        $this->registerService(ThemeMapper::class, function (Container $container): ThemeMapper {
            return new ThemeMapper();
        });

        // Then repository.
        $this->registerTagAndService(ThemeRepositoryInterface::TAG_THEME_REPOSITORY, ThemeRepository::class,
            function (Container $container): ThemeRepositoryInterface {
                /** @var Configuration $configuration */
                $configuration = $container[Configuration::class];
                /** @var ThemeMapper $themeMapper */
                $themeMapper = $container[ThemeMapper::class];

                return new ThemeRepository($configuration, $themeMapper);
            });

        // Then use a lazy service.
        $this->registerServiceLazy(ThemeRepositoryInterface::class, ThemeRepository::class);

        // Then proxy.
        $this->container->extend(ThemeRepositoryInterface::class,
            function (ThemeRepositoryInterface $themeRepository, Container $container): ThemeRepositoryInterface {
                /** @var CacheInterface $cache */
                $cache = $container[CacheInterface::class];
                /** @var ThemeMapper $themeMapper */
                $themeMapper = $container[ThemeMapper::class];

                return new CacheProxyThemeRepository($cache, $themeRepository, $themeMapper);
            });

    }

    /**
     * @TODO The formatting in here is really messed up. The closure functions should not break the line.
     * @throws PackageException
     */
    private function registerDataLayerPostConfiguration(): void
    {
        // First register mapper.
        $this->registerService(PostConfigurationMetaCustomMapper::class,
            function (Container $container): PostConfigurationMetaCustomMapper {
                return new PostConfigurationMetaCustomMapper();
            });

        $this->registerService(PostConfigurationMetaMapper::class,
            function (Container $container): PostConfigurationMetaMapper {
                /** @var PostConfigurationMetaCustomMapper $postConfigurationMetaCustomMapper */
                $postConfigurationMetaCustomMapper = $container[PostConfigurationMetaCustomMapper::class];

                return new PostConfigurationMetaMapper($postConfigurationMetaCustomMapper);
            });

        $this->registerService(PostConfigurationLocaleMapper::class,
            function (Container $container): PostConfigurationLocaleMapper {
                /** @var PostConfigurationMetaMapper $postConfigurationMetaMapper */
                $postConfigurationMetaMapper = $container[PostConfigurationMetaMapper::class];

                return new PostConfigurationLocaleMapper($postConfigurationMetaMapper);
            });

        $this->registerService(PostConfigurationMapper::class,
            function (Container $container): PostConfigurationMapper {
                /** @var PostConfigurationLocaleMapper $postConfigurationLocaleMapper */
                $postConfigurationLocaleMapper = $container[PostConfigurationLocaleMapper::class];

                return new PostConfigurationMapper($postConfigurationLocaleMapper);
            });

        // Register the Post Repository for File implementation.
        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY,
            PostConfigurationRepositoryFile::class,
            function (Container $container): PostConfigurationRepositoryInterface {
                /** @var Configuration $configuration */
                $configuration = $container[Configuration::class];
                /** @var PostConfigurationMapper $postConfigurationMapper */
                $postConfigurationMapper = $container[PostConfigurationMapper::class];
                /** @var LoggerInterface $logger */
                $logger = $container[LoggerInterface::class];

                return new PostConfigurationRepositoryFile($configuration, $postConfigurationMapper, $logger);
            });

        // Then alias the implementation.
        $this->registerServiceAlias(PostConfigurationRepositoryInterface::class,
            PostConfigurationRepositoryFile::class);
//        $this->registerServiceAlias(PostConfigurationRepositoryInterface::class, PostConfigurationLocaleRepository::class);

        // Register the Content Repository for File implementations, but using locales
        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY,
            PostConfigurationLocaleRepository::class,
            function (Container $container): PostConfigurationRepositoryInterface {
                /** @var PostConfigurationRepositoryFile $postConfigurationRepository */
                $postConfigurationRepository = $container[PostConfigurationRepositoryFile::class];
                /** @var LoggerInterface $logger */
                $logger = null; //$container[LoggerInterface::class]; ToDo: Logger is defined in /src/Package.php, it ain't defined here yet?
                // ToDo: Inject default locale into below repository.
                return new PostConfigurationLocaleRepository($postConfigurationRepository, $logger);
            });

        // Register the Aggregation ContentRepository
        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY,
            PostConfigurationRepositoryAggregation::class,
            function (Container $container): PostConfigurationRepositoryInterface {
                $classList = $this->resolveTag(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY,
                    PostConfigurationRepositoryInterface::class, [PostConfigurationRepositoryAggregation::class]);

                return new PostConfigurationRepositoryAggregation($classList);
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
        $this->registerService(PostConfigurationService::class,
            function (Container $container): PostConfigurationService {
                /** @var Configuration $configuration */
                $configuration = $container[Configuration::class];
                /** @var PostConfigurationRepositoryInterface $postConfigurationRepository */
                $postConfigurationRepository = $container[PostConfigurationRepositoryInterface::class];

                return new PostConfigurationService($configuration, $postConfigurationRepository);
            });
    }

    /**
     * @param string $serviceName
     * @param string $serviceNameLazy
     * @throws PackageException
     */
    protected function registerServiceLazy(string $serviceName, string $serviceNameLazy): void
    {
        $this->registerConfiguration($serviceName, [
            'implementation' => $serviceNameLazy,
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService($serviceName, function (Container $container) use ($configuration, $serviceName) {
            /** @var array $settings */
            $settings = $configuration[$serviceName];

            return $container[$settings['implementation']];
        });
    }
}
