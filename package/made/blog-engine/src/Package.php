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

namespace Made\Blog\Engine;

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\Implementation\Aggregation\PostConfigurationRepository as PostConfigurationRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository as ThemeRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\PostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Implementation\PostRepository;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaCustomMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaMapper;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\PostContentProvider as PostContentProviderFile;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\RenderParsedownTask;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\RenderTwigTask;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\WrapContextTask;
use Made\Blog\Engine\Service\PostContentProviderInterface;
use Made\Blog\Engine\Service\PostContentResolver;
use Made\Blog\Engine\Service\PostContentResolverInterface;
use Made\Blog\Engine\Service\PostService;
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;
use Made\Blog\Engine\Service\ThemeService;
use Parsedown;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

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

        $this->registerPostService();

        $this->registerTwig();
        $this->registerParsedown();

        $this->registerDataLayerTheme();
        $this->registerDataLayerPostConfiguration();
        $this->registerDataLayerPost();

        $this->registerThemeService();
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
            Configuration::CONFIGURATION_NAME_FALLBACK_LOCALE => 'en',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Configuration::class, function (Container $container) use ($configuration): Configuration {
            /** @var array $settings */
            $settings = $configuration[Configuration::class];

            return (new Configuration())
                ->setRootDirectory($settings[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY])
                ->setFallbackLocale($settings[Configuration::CONFIGURATION_NAME_FALLBACK_LOCALE]);
        });
    }

    private function registerPostService(): void
    {
        $this->registerService(PostService::class, function (Container $container): PostService {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];

            return new PostService($configuration);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerTwig(): void
    {
        $this->registerConfiguration(Environment::class, [
            // TODO: Complete option list with defaults.
            'cache' => false,
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(FilesystemLoader::class, function (Container $container): LoaderInterface {
            /** @var PostService $postService */
            $postService = $container[PostService::class];

            $loader = new FilesystemLoader();
            $postService->updateLoader($loader);

            return $loader;
        });

        $this->registerServiceAlias(LoaderInterface::class, FilesystemLoader::class);

        $this->registerService(Environment::class, function (Container $container) use ($configuration): Environment {
            /** @var array $settings */
            $settings = $configuration[Environment::class];

            /** @var LoaderInterface $loader */
            $loader = $container[LoaderInterface::class];

            return new Environment($loader, $settings);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerParsedown(): void
    {
        $this->registerConfiguration(Parsedown::class, [
            'breaks_enabled' => true,
            'markup_escaped' => false,
            'urls_linked' => true,
            'safe_mode' => false,
        ]);

        /** @var array $configuration */
        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Parsedown::class, function (Container $container) use ($configuration): Parsedown {
            /** @var array $settings */
            $settings = $configuration[Parsedown::class];

            /** @var Parsedown $parsedown */
            $parsedown = new Parsedown();
            $parsedown->setBreaksEnabled($settings['breaks_enabled']);
            $parsedown->setMarkupEscaped($settings['markup_escaped']);
            $parsedown->setUrlsLinked($settings['urls_linked']);
            $parsedown->setSafeMode($settings['safe_mode']);

            return $parsedown;
        });

        // TODO: Extend with "parsedown-extra".
    }

    /**
     * @throws PackageException
     */
    private function registerDataLayerTheme(): void
    {
        $this->registerService(ThemeMapper::class, function (Container $container): ThemeMapper {
            return new ThemeMapper();
        });

        $this->registerTagAndService(ThemeRepositoryInterface::TAG_THEME_REPOSITORY, ThemeRepositoryFile::class, function (Container $container): ThemeRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ThemeMapper $themeMapper */
            $themeMapper = $container[ThemeMapper::class];

            return new ThemeRepositoryFile($configuration, $themeMapper);
        });

        $this->registerServiceLazy(ThemeRepositoryInterface::class, ThemeRepositoryFile::class);

        $this->container->extend(ThemeRepositoryInterface::class, function (ThemeRepositoryInterface $themeRepository, Container $container): ThemeRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyThemeRepository($cache, $themeRepository);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerDataLayerPostConfiguration(): void
    {
        $this->registerService(PostConfigurationMetaCustomMapper::class, function (Container $container): PostConfigurationMetaCustomMapper {
            return new PostConfigurationMetaCustomMapper();
        });

        $this->registerService(PostConfigurationMetaMapper::class, function (Container $container): PostConfigurationMetaMapper {
            /** @var PostConfigurationMetaCustomMapper $postConfigurationMetaCustomMapper */
            $postConfigurationMetaCustomMapper = $container[PostConfigurationMetaCustomMapper::class];

            return new PostConfigurationMetaMapper($postConfigurationMetaCustomMapper);
        });

        $this->registerService(PostConfigurationLocaleMapper::class, function (Container $container): PostConfigurationLocaleMapper {
            /** @var PostConfigurationMetaMapper $postConfigurationMetaMapper */
            $postConfigurationMetaMapper = $container[PostConfigurationMetaMapper::class];

            return new PostConfigurationLocaleMapper($postConfigurationMetaMapper);
        });

        $this->registerService(PostConfigurationMapper::class, function (Container $container): PostConfigurationMapper {
            /** @var PostConfigurationLocaleMapper $postConfigurationLocaleMapper */
            $postConfigurationLocaleMapper = $container[PostConfigurationLocaleMapper::class];

            return new PostConfigurationMapper($postConfigurationLocaleMapper);
        });

        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryFile::class, function (Container $container): PostConfigurationRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var PostConfigurationMapper $postConfigurationMapper */
            $postConfigurationMapper = $container[PostConfigurationMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new PostConfigurationRepositoryFile($configuration, $postConfigurationMapper, $logger);
        });

        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryAggregation::class, function (Container $container): PostConfigurationRepositoryInterface {
            $serviceList = $this->resolveTag(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryInterface::class, [
                PostConfigurationRepositoryAggregation::class,
            ]);

            return new PostConfigurationRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(PostConfigurationRepositoryInterface::class, PostConfigurationRepositoryAggregation::class);

        $this->container->extend(PostConfigurationRepositoryInterface::class, function (PostConfigurationRepositoryInterface $postConfigurationRepository, Container $container): PostConfigurationRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyPostConfigurationRepository($cache, $postConfigurationRepository);
        });

        $this->registerTagAndService(PostConfigurationLocaleRepositoryInterface::TAG_POST_CONFIGURATION_LOCALE_REPOSITORY, PostConfigurationLocaleRepository::class, function (Container $container): PostConfigurationLocaleRepositoryInterface {
            /** @var PostConfigurationRepositoryInterface $postConfigurationRepository */
            $postConfigurationRepository = $container[PostConfigurationRepositoryInterface::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new PostConfigurationLocaleRepository($postConfigurationRepository, $logger);
        });

        $this->registerServiceLazy(PostConfigurationLocaleRepositoryInterface::class, PostConfigurationLocaleRepository::class);

        $this->container->extend(PostConfigurationLocaleRepositoryInterface::class, function (PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository, Container $container): PostConfigurationLocaleRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyPostConfigurationLocaleRepository($cache, $postConfigurationLocaleRepository);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerDataLayerPost(): void
    {
        $this->registerConfiguration(TaskAbstract::class, [
            WrapContextTask::class => 10,
            RenderTwigTask::class => 20,
            RenderParsedownTask::class => 30,
        ]);

        /** @var array $configuration */
        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerTagAndService(PostContentProviderFile::TAG_POST_CONTENT_PROVIDER_TASK, WrapContextTask::class, function (Container $container) use ($configuration): TaskAbstract {
            /** @var array $settings */
            $settings = $configuration[TaskAbstract::class];

            return new WrapContextTask($settings[WrapContextTask::class]);
        });

        $this->registerTagAndService(PostContentProviderFile::TAG_POST_CONTENT_PROVIDER_TASK, RenderTwigTask::class, function (Container $container) use ($configuration): TaskAbstract {
            /** @var array $settings */
            $settings = $configuration[TaskAbstract::class];

            /** @var PostService $postService */
            $postService = $container[PostService::class];
            /** @var Environment $environment */
            $environment = $container[Environment::class];

            return new RenderTwigTask($settings[RenderTwigTask::class], $postService, $environment);
        });

        $this->registerTagAndService(PostContentProviderFile::TAG_POST_CONTENT_PROVIDER_TASK, RenderParsedownTask::class, function (Container $container) use ($configuration): TaskAbstract {
            /** @var array $settings */
            $settings = $configuration[TaskAbstract::class];

            // TODO: Use "parsedown-extra" instead.
            /** @var Parsedown $parsedown */
            $parsedown = $container[Parsedown::class];

            return new RenderParsedownTask($settings[RenderParsedownTask::class], $parsedown);
        });

        $this->registerTagAndService(PostContentProviderInterface::TAG_POST_CONTENT_PROVIDER, PostContentProviderFile::class, function (Container $container): PostContentProviderInterface {
            /** @var array|TaskAbstract[] $serviceList */
            $serviceList = $this->resolveTag(PostContentProviderFile::TAG_POST_CONTENT_PROVIDER_TASK, TaskAbstract::class, null);

            return new PostContentProviderFile($serviceList);
        });

        $this->registerService(PostContentResolver::class, function (Container $container): PostContentResolver {
            /** @var array|PostContentProviderInterface[] $serviceList */
            $serviceList = $this->resolveTag(PostContentProviderInterface::TAG_POST_CONTENT_PROVIDER, PostContentProviderInterface::class, null);

            return new PostContentResolver($serviceList);
        });

        $this->registerServiceAlias(PostContentResolverInterface::class, PostContentResolver::class);

        $this->registerTagAndService(PostRepositoryInterface::TAG_POST_REPOSITORY, PostRepository::class, function (Container $container): PostRepositoryInterface {
            /** @var PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository */
            $postConfigurationLocaleRepository = $container[PostConfigurationLocaleRepositoryInterface::class];
            /** @var PostContentResolverInterface $postContentResolver */
            $postContentResolver = $container[PostContentResolverInterface::class];

            return new PostRepository($postConfigurationLocaleRepository, $postContentResolver);
        });

        $this->registerServiceLazy(PostRepositoryInterface::class, PostRepository::class);

        $this->container->extend(PostRepositoryInterface::class, function (PostRepositoryInterface $postRepository, Container $container): PostRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyPostRepository($cache, $postRepository);
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
}
