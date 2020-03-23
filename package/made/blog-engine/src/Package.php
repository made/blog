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
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Implementation\Aggregation\CategoryRepository as CategoryRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\PostConfigurationLocaleRepository as PostConfigurationLocaleRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\TagRepository as TagRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\File\CategoryRepository as CategoryRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationLocaleRepository as PostConfigurationLocaleRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\TagRepository as TagRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository as ThemeRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\PostRepository;
use Made\Blog\Engine\Repository\Mapper\CategoryMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaCustomMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMetaMapper;
use Made\Blog\Engine\Repository\Mapper\PostContentMapper;
use Made\Blog\Engine\Repository\Mapper\PostMapper;
use Made\Blog\Engine\Repository\Mapper\TagMapper;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Repository\Proxy\CacheProxyCategoryRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyTagRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\PostContentProvider as PostContentProviderFile;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\RenderParsedownTask;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\RenderTwigTask;
use Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task\WrapContextTask;
use Made\Blog\Engine\Service\PostContentProviderInterface;
use Made\Blog\Engine\Service\PostContentResolver;
use Made\Blog\Engine\Service\PostContentResolverInterface;
use Made\Blog\Engine\Service\PostService;
use Made\Blog\Engine\Service\SlugParser\Implementation\Basic\SlugParser;
use Made\Blog\Engine\Service\SlugParserInterface;
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;
use Made\Blog\Engine\Service\ThemeService;
use ParsedownExtra;
use Pimple\Container;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use TaskChain\TaskChain;
use TaskChain\TaskChainInterface;
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

        $this->registerSlugParser();
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
            'debug' => false,
            'charset' => 'UTF-8',
            'strict_variables' => false,
            'autoescape' => 'html',
            'cache' => false,
            'auto_reload' => null,
            'optimizations' => -1,
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
        $this->registerConfiguration(ParsedownExtra::class, [
            'breaks_enabled' => true,
            'markup_escaped' => false,
            'urls_linked' => true,
            'safe_mode' => false,
        ]);

        /** @var array $configuration */
        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(ParsedownExtra::class, function (Container $container) use ($configuration): ParsedownExtra {
            /** @var array $settings */
            $settings = $configuration[ParsedownExtra::class];

            /** @var ParsedownExtra $parsedown */
            $parsedown = new ParsedownExtra();
            $parsedown->setBreaksEnabled($settings['breaks_enabled']);
            $parsedown->setMarkupEscaped($settings['markup_escaped']);
            $parsedown->setUrlsLinked($settings['urls_linked']);
            $parsedown->setSafeMode($settings['safe_mode']);

            return $parsedown;
        });
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
        $this->registerConfiguration(PostConfigurationRepositoryFile::class, [
            'default' => [],
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(CategoryMapper::class, function (Container $container): CategoryMapper {
            return new CategoryMapper();
        });

        $this->registerService(TagMapper::class, function (Container $container): TagMapper {
            return new TagMapper();
        });

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
            /** @var CategoryMapper $categoryMapper */
            $categoryMapper = $container[CategoryMapper::class];
            /** @var TagMapper $tagMapper */
            $tagMapper = $container[TagMapper::class];

            return new PostConfigurationLocaleMapper($postConfigurationMetaMapper, $categoryMapper, $tagMapper);
        });

        $this->registerService(PostConfigurationMapper::class, function (Container $container): PostConfigurationMapper {
            /** @var PostConfigurationLocaleMapper $postConfigurationLocaleMapper */
            $postConfigurationLocaleMapper = $container[PostConfigurationLocaleMapper::class];

            return new PostConfigurationMapper($postConfigurationLocaleMapper);
        });

        $this->registerService(PostContentMapper::class, function (Container $container): PostContentMapper {
            return new PostContentMapper();
        });

        $this->registerService(PostMapper::class, function (Container $container): PostMapper {
            /** @var PostConfigurationMapper $postConfigurationMapper */
            $postConfigurationMapper = $container[PostConfigurationMapper::class];
            /** @var PostContentMapper $postContentMapper */
            $postContentMapper = $container[PostContentMapper::class];

            return new PostMapper($postConfigurationMapper, $postContentMapper);
        });

        $this->registerTagAndService(CategoryRepositoryInterface::TAG_CATEGORY_REPOSITORY, CategoryRepositoryFile::class, function (Container $container): CategoryRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var CategoryMapper $categoryMapper */
            $categoryMapper = $container[CategoryMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CategoryRepositoryFile($configuration, $categoryMapper, $logger);
        });

        $this->registerTagAndService(CategoryRepositoryInterface::TAG_CATEGORY_REPOSITORY, CategoryRepositoryAggregation::class, function (Container $container): CategoryRepositoryInterface {
            /** @var array|CategoryRepositoryInterface[] $serviceList */
            $serviceList = $this->resolveTag(CategoryRepositoryInterface::TAG_CATEGORY_REPOSITORY, CategoryRepositoryInterface::class, [
                CategoryRepositoryAggregation::class,
            ]);

            return new CategoryRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(CategoryRepositoryInterface::class, CategoryRepositoryAggregation::class);

        $this->container->extend(CategoryRepositoryInterface::class, function (CategoryRepositoryInterface $categoryRepository, Container $container): CategoryRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyCategoryRepository($cache, $categoryRepository);
        });

        $this->registerTagAndService(TagRepositoryInterface::TAG_TAG_REPOSITORY, TagRepositoryFile::class, function (Container $container): TagRepositoryInterface {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var TagMapper $tagMapper */
            $tagMapper = $container[TagMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new TagRepositoryFile($configuration, $tagMapper, $logger);
        });

        $this->registerTagAndService(TagRepositoryInterface::TAG_TAG_REPOSITORY, TagRepositoryAggregation::class, function (Container $container): TagRepositoryInterface {
            /** @var array|TagRepositoryInterface[] $serviceList */
            $serviceList = $this->resolveTag(TagRepositoryInterface::TAG_TAG_REPOSITORY, TagRepositoryInterface::class, [
                TagRepositoryAggregation::class,
            ]);

            return new TagRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(TagRepositoryInterface::class, TagRepositoryAggregation::class);

        $this->container->extend(TagRepositoryInterface::class, function (TagRepositoryInterface $tagRepository, Container $container): TagRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyTagRepository($cache, $tagRepository);
        });

        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryFile::class, function (Container $container) use ($configuration): PostConfigurationRepositoryInterface {
            /** @var array $settings */
            $settings = $configuration[PostConfigurationRepositoryFile::class];

            unset($configuration);

            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var PostConfigurationMapper $postConfigurationMapper */
            $postConfigurationMapper = $container[PostConfigurationMapper::class];
            /** @var CategoryRepositoryInterface $categoryRepository */
            $categoryRepository = $container[CategoryRepositoryInterface::class];
            /** @var CategoryMapper $categoryMapper */
            $categoryMapper = $container[CategoryMapper::class];
            /** @var TagRepositoryInterface $tagRepository */
            $tagRepository = $container[TagRepositoryInterface::class];
            /** @var TagMapper $tagMapper */
            $tagMapper = $container[TagMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new PostConfigurationRepositoryFile($settings['default'], $configuration, $postConfigurationMapper, $categoryRepository, $categoryMapper, $tagRepository, $tagMapper, $logger);
        });

        $this->registerServiceLazy(PostConfigurationRepositoryInterface::class, PostConfigurationRepositoryFile::class);

        $this->container->extend(PostConfigurationRepositoryInterface::class, function (PostConfigurationRepositoryInterface $postConfigurationRepository, Container $container): PostConfigurationRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];

            return new CacheProxyPostConfigurationRepository($cache, $postConfigurationRepository);
        });

        $this->registerTagAndService(PostConfigurationLocaleRepositoryInterface::TAG_POST_CONFIGURATION_LOCALE_REPOSITORY, PostConfigurationLocaleRepositoryFile::class, function (Container $container): PostConfigurationLocaleRepositoryInterface {
            /** @var PostConfigurationRepositoryFile $postConfigurationRepository */
            $postConfigurationRepository = $container[PostConfigurationRepositoryFile::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new PostConfigurationLocaleRepositoryFile($postConfigurationRepository, $logger);
        });

        $this->registerTagAndService(PostConfigurationLocaleRepositoryInterface::TAG_POST_CONFIGURATION_LOCALE_REPOSITORY, PostConfigurationLocaleRepositoryAggregation::class, function (Container $container): PostConfigurationLocaleRepositoryInterface {
            /** @var array|PostConfigurationLocaleRepositoryInterface[] $serviceList */
            $serviceList = $this->resolveTag(PostConfigurationLocaleRepositoryInterface::TAG_POST_CONFIGURATION_LOCALE_REPOSITORY, PostConfigurationLocaleRepositoryInterface::class, [
                PostConfigurationLocaleRepositoryAggregation::class,
            ]);

            return new PostConfigurationLocaleRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(PostConfigurationLocaleRepositoryInterface::class, PostConfigurationLocaleRepositoryAggregation::class);

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

        $this->registerService(TaskChain::class, $this->container->factory(function (Container $container): TaskChain {
            // TODO: Make configurable.
            return new TaskChain(false);
        }));

        // This has to be a factory, since the returned value must not be cached in the container!
        $this->registerService(TaskChainInterface::class, $this->container->factory($this->container->raw(TaskChain::class)));

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

            /** @var ParsedownExtra $parsedown */
            $parsedown = $container[ParsedownExtra::class];

            return new RenderParsedownTask($settings[RenderParsedownTask::class], $parsedown);
        });

        $this->registerTagAndService(PostContentProviderInterface::TAG_POST_CONTENT_PROVIDER, PostContentProviderFile::class, function (Container $container): PostContentProviderInterface {
            /** @var TaskChainInterface $taskChain */
            $taskChain = $container[TaskChainInterface::class];
            /** @var array|TaskAbstract[] $serviceList */
            $serviceList = $this->resolveTag(PostContentProviderFile::TAG_POST_CONTENT_PROVIDER_TASK, TaskAbstract::class, null);

            return new PostContentProviderFile($taskChain, $serviceList);
        });

        $this->registerService(PostContentResolver::class, function (Container $container): PostContentResolver {
            /** @var array|PostContentProviderInterface[] $serviceList */
            $serviceList = $this->resolveTag(PostContentProviderInterface::TAG_POST_CONTENT_PROVIDER, PostContentProviderInterface::class, null);

            return new PostContentResolver($serviceList);
        });

        $this->registerServiceAlias(PostContentResolverInterface::class, PostContentResolver::class);

        $this->registerTagAndService(PostRepositoryInterface::TAG_POST_REPOSITORY, PostRepository::class, function (Container $container): PostRepositoryInterface {
            /** @var PostConfigurationRepositoryInterface $postConfigurationRepository */
            $postConfigurationRepository = $container[PostConfigurationRepositoryInterface::class];
            /** @var PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository */
            $postConfigurationLocaleRepository = $container[PostConfigurationLocaleRepositoryInterface::class];
            /** @var PostContentResolverInterface $postContentResolver */
            $postContentResolver = $container[PostContentResolverInterface::class];

            return new PostRepository($postConfigurationRepository, $postConfigurationLocaleRepository, $postContentResolver);
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

    private function registerSlugParser(): void
    {
        $this->registerService(SlugParser::class, function (Container $container): SlugParserInterface {
            return new SlugParser();
        });

        $this->registerServiceAlias(SlugParserInterface::class, SlugParser::class);
    }
}
