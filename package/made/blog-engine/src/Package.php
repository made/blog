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
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Implementation\Aggregation\AuthorRepository as AuthorRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\CategoryRepository as CategoryRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\PostConfigurationLocaleRepository as PostConfigurationLocaleRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\PostConfigurationRepository as PostConfigurationRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\Aggregation\TagRepository as TagRepositoryAggregation;
use Made\Blog\Engine\Repository\Implementation\File\AuthorRepository as AuthorRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\CategoryRepository as CategoryRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationLocaleRepository as PostConfigurationLocaleRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\TagRepository as TagRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\File\ThemeRepository as ThemeRepositoryFile;
use Made\Blog\Engine\Repository\Implementation\PostRepository;
use Made\Blog\Engine\Repository\Mapper\AuthorMapper;
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
use Made\Blog\Engine\Repository\Proxy\CacheProxyAuthorRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyCategoryRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationLocaleRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostConfigurationRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyPostRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyTagRepository;
use Made\Blog\Engine\Repository\Proxy\CacheProxyThemeRepository;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\PathService;
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
     * @inheritDoc
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

        $this->registerPathService();

        $this->registerPostService();

        $this->registerTwig();
        $this->registerParsedown();

        $this->registerDataLayerTheme();
        $this->registerDataLayerPostConfiguration();

        $this->registerPostContentResolver();

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
            Configuration::CONFIGURATION_NAME_THEME_NAME => 'theme-basic',
        ]);

        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(Configuration::class, function (Container $container) use ($configuration): Configuration {
            /** @var array $settings */
            $settings = $configuration[Configuration::class];

            return (new Configuration())
                ->setRootDirectory($settings[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY])
                ->setFallbackLocale($settings[Configuration::CONFIGURATION_NAME_FALLBACK_LOCALE])
                ->setThemeName($settings[Configuration::CONFIGURATION_NAME_THEME_NAME]);
        });
    }

    private function registerPathService(): void
    {
        $this->registerService(PathService::class, function (Container $container): PathService {
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];

            return new PathService($configuration);
        });
    }

    private function registerPostService(): void
    {
        $this->registerService(PostService::class, function (Container $container): PostService {
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];

            return new PostService($pathService);
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
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
            /** @var ThemeMapper $themeMapper */
            $themeMapper = $container[ThemeMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new ThemeRepositoryFile($pathService, $themeMapper, $logger);
        });

        $this->registerServiceLazy(ThemeRepositoryInterface::class, ThemeRepositoryFile::class);

        $this->container->extend(ThemeRepositoryInterface::class, function (ThemeRepositoryInterface $themeRepository, Container $container): ThemeRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyThemeRepository($cache, $themeRepository, $logger);
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

        $this->registerService(AuthorMapper::class, function (Container $container): AuthorMapper {
            return new AuthorMapper();
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
            /** @var AuthorMapper $authorMapper */
            $authorMapper = $container[AuthorMapper::class];
            /** @var PostConfigurationMetaMapper $postConfigurationMetaMapper */
            $postConfigurationMetaMapper = $container[PostConfigurationMetaMapper::class];
            /** @var CategoryMapper $categoryMapper */
            $categoryMapper = $container[CategoryMapper::class];
            /** @var TagMapper $tagMapper */
            $tagMapper = $container[TagMapper::class];

            return new PostConfigurationLocaleMapper($authorMapper, $postConfigurationMetaMapper, $categoryMapper, $tagMapper);
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
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
            /** @var CategoryMapper $categoryMapper */
            $categoryMapper = $container[CategoryMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CategoryRepositoryFile($pathService, $categoryMapper, $logger);
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
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyCategoryRepository($cache, $categoryRepository, $logger);
        });

        $this->registerTagAndService(TagRepositoryInterface::TAG_TAG_REPOSITORY, TagRepositoryFile::class, function (Container $container): TagRepositoryInterface {
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
            /** @var TagMapper $tagMapper */
            $tagMapper = $container[TagMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new TagRepositoryFile($pathService, $tagMapper, $logger);
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
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyTagRepository($cache, $tagRepository, $logger);
        });

        $this->registerTagAndService(AuthorRepositoryInterface::TAG_AUTHOR_REPOSITORY, AuthorRepositoryFile::class, function (Container $container): AuthorRepositoryInterface {
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
            /** @var AuthorMapper $authorMapper */
            $authorMapper = $container[AuthorMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new AuthorRepositoryFile($pathService, $authorMapper, $logger);
        });

        $this->registerTagAndService(AuthorRepositoryInterface::TAG_AUTHOR_REPOSITORY, AuthorRepositoryAggregation::class, function (Container $container): AuthorRepositoryInterface {
            /** @var array|AuthorRepositoryInterface[] $serviceList */
            $serviceList = $this->resolveTag(AuthorRepositoryInterface::TAG_AUTHOR_REPOSITORY, AuthorRepositoryInterface::class, [
                AuthorRepositoryAggregation::class,
            ]);

            return new AuthorRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(AuthorRepositoryInterface::class, AuthorRepositoryAggregation::class);

        $this->container->extend(AuthorRepositoryInterface::class, function (AuthorRepositoryInterface $authorRepository, Container $container): AuthorRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyAuthorRepository($cache, $authorRepository, $logger);
        });

        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryFile::class, function (Container $container) use ($configuration): PostConfigurationRepositoryInterface {
            /** @var array $settings */
            $settings = $configuration[PostConfigurationRepositoryFile::class];

            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
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
            /** @var AuthorRepositoryInterface $authorRepository */
            $authorRepository = $container[AuthorRepositoryInterface::class];
            /** @var AuthorMapper $authorMapper */
            $authorMapper = $container[AuthorMapper::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new PostConfigurationRepositoryFile($settings['default'], $pathService, $postConfigurationMapper, $categoryRepository, $categoryMapper, $tagRepository, $tagMapper, $authorRepository, $authorMapper, $logger);
        });

        $this->registerTagAndService(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryAggregation::class, function (Container $container): PostConfigurationRepositoryInterface {
            /** @var array|PostConfigurationRepositoryInterface[] $serviceList */
            $serviceList = $this->resolveTag(PostConfigurationRepositoryInterface::TAG_POST_CONFIGURATION_REPOSITORY, PostConfigurationRepositoryInterface::class, [
                PostConfigurationRepositoryAggregation::class,
            ]);

            return new PostConfigurationRepositoryAggregation($serviceList);
        });

        $this->registerServiceLazy(PostConfigurationRepositoryInterface::class, PostConfigurationRepositoryAggregation::class);

        $this->container->extend(PostConfigurationRepositoryInterface::class, function (PostConfigurationRepositoryInterface $postConfigurationRepository, Container $container): PostConfigurationRepositoryInterface {
            /** @var CacheInterface $cache */
            $cache = $container[CacheInterface::class];
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyPostConfigurationRepository($cache, $postConfigurationRepository, $logger);
        });

        $this->registerTagAndService(PostConfigurationLocaleRepositoryInterface::TAG_POST_CONFIGURATION_LOCALE_REPOSITORY, PostConfigurationLocaleRepositoryFile::class, function (Container $container): PostConfigurationLocaleRepositoryInterface {
            // The file implementation only needs the file implementation!
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
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyPostConfigurationLocaleRepository($cache, $postConfigurationLocaleRepository, $logger);
        });
    }

    /**
     * @throws PackageException
     */
    private function registerPostContentResolver(): void
    {
        $this->registerConfiguration(TaskAbstract::class, [
            WrapContextTask::class => 10,
            RenderTwigTask::class => 20,
            RenderParsedownTask::class => 30,
        ]);

        /** @var array $configuration */
        $configuration = $this->container[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService(TaskChain::class, $this->container->factory(function (Container $container): TaskChain {
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
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new RenderTwigTask($settings[RenderTwigTask::class], $postService, $environment, $logger);
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

        $this->registerService(PostContentResolver::class, function (Container $container): PostContentResolverInterface {
            /** @var array|PostContentProviderInterface[] $serviceList */
            $serviceList = $this->resolveTag(PostContentProviderInterface::TAG_POST_CONTENT_PROVIDER, PostContentProviderInterface::class, null);

            return new PostContentResolver($serviceList);
        });

        $this->registerServiceAlias(PostContentResolverInterface::class, PostContentResolver::class);
    }

    /**
     * @throws PackageException
     */
    private function registerDataLayerPost(): void
    {
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
            /** @var LoggerInterface $logger */
            $logger = $container[LoggerInterface::class];

            return new CacheProxyPostRepository($cache, $postRepository, $logger);
        });
    }

    private function registerThemeService(): void
    {
        $this->registerService(ThemeService::class, function (Container $container): ThemeService {
            /** @var PathService $pathService */
            $pathService = $container[PathService::class];
            /** @var Configuration $configuration */
            $configuration = $container[Configuration::class];
            /** @var ThemeRepositoryInterface $themeRepository */
            $themeRepository = $container[ThemeRepositoryInterface::class];

            return new ThemeService($configuration, $pathService, $themeRepository);
        });
    }
}
