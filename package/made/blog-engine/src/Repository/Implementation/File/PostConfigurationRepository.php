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

namespace Made\Blog\Engine\Repository\Implementation\File;

use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Help\Directory;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\PostConfigurationFileRepositoryInterface;
use Made\Blog\Engine\Service\PostConfigurationService;
use Psr\Log\LoggerInterface;

class PostConfigurationRepository implements PostConfigurationFileRepositoryInterface
{
    // ToDo: array_column to get a summary of the categories and tags of all posts :)
    //  for Methods like getAllCategories() or getAllTags()
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PostConfigurationMapper
     */
    private $postConfigurationMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostConfigurationRepository constructor.
     * @param Configuration $configuration
     * @param PostConfigurationMapper $postConfigurationMapper
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, PostConfigurationMapper $postConfigurationMapper, ?LoggerInterface $logger)
    {
        // ToDo: Inject the default locale
        $this->configuration = $configuration;
        $this->postConfigurationMapper = $postConfigurationMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $path = $this->getPath();

        $list = Directory::listCallback($path, function (string $entry): bool {
            // ToDo: Maybe put this in the Directory helper class?
            if ('.' === $entry || '..' === $entry || '.DS_Store' === $entry || '.gitignore' === $entry) {
                return false;
            }

            $postConfigurationPath = $this->getPostPath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            $requiredFilesExist = is_dir($postConfigurationPath) && is_file($configurationPath);

            if (!$requiredFilesExist) {
                var_dump($postConfigurationPath);
                var_dump($configurationPath);
                throw new \Exception('Something ain`t right with the configuration for this blog post, sir.');
                // ToDo: As soon logging works, remove exception and use logging below
//                $this->logger->warning('Something is not right with the configuration for this blog post.', [
//                    "content_path" => $contentPath,
//                    'configuration_path' => $configurationPath
//                ]);
            }

            return $requiredFilesExist;
        });

        $all = array_map(function (string $entry): ?PostConfiguration {
            $configurationPath = $this->getConfigurationPath($entry);

            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            try {
                return $this->postConfigurationMapper->fromData($data);
            } catch (PostConfigurationException $exception) {
                throw $exception;
                // ToDo: As soon logging works activate it again :)
//                $this->logger->error($exception->getMessage(), [
//                    'data' => $entry
//                ]);
            }

            return null;
        }, $list);

        return array_values($all);
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $slug, string $locale = null): ?PostConfiguration
    {
        if ($locale === null) {
            // ToDo: LocaleService should be injected into the constructor and used as class property
            //  $this->locale = $localeService->getLocale();
            $locale = 'en';
        }

        $all = $this->getAll();

        return array_reduce($all, function (?PostConfiguration $carry, PostConfiguration $postConfiguration) use ($slug, $locale): ?PostConfiguration {
            if (!isset($postConfiguration->getLocale()[$locale])) {
                throw new PostConfigurationException('Unfortunately no posts found for this locale.');
            }

            $slugInCurrentLocale = $postConfiguration->getLocale()[$locale]->getSlug();

            if (null === $carry && $slugInCurrentLocale === $slug) {
                $carry = $postConfiguration;
            }

            return $carry;
        }, null);
        // ToDo: Maybe think about also searching for redirect slugs if nothing is found above.
        //  Maybe an extra Repository Layer for this (Proxy).
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $slugRedirect, string $locale = null): ?PostConfiguration
    {
        if ($locale === null) {
            // ToDo: LocaleService should be injected into the constructor and used as class property
            //  $this->locale = $localeService->getLocale();
            $locale = 'en';
        }

        $all = $this->getAll();

        return array_reduce($all, function (?PostConfiguration $carry, PostConfiguration $postConfiguration) use ($slugRedirect, $locale): ?PostConfiguration {
            if (!isset($postConfiguration->getLocale()[$locale])) {
                throw new PostConfigurationException('Unfortunately no posts found for this locale.');
            }

            $redirectInCurrentLocale = $postConfiguration->getLocale()[$locale]->getRedirect();

            if (null === $carry && in_array($slugRedirect, $redirectInCurrentLocale)) {
                $carry = $postConfiguration;
            }

            return $carry;
        }, null);
    }

    /**
     * // ToDo: Only english locale works at the moment when calling the method directly
     * @inheritDoc
     */
    public function getAllByCategory(string ...$category): array
    {
        // ToDo: Maybe also find a way to override the locale with a function parameter
        //  Since splat operator is used, no optional parameters can be passed
        //  Like this the function ain't that flexible

        // ToDo: LocaleService should be injected into the constructor and used as class property
        //  $this->locale = $localeService->getLocale();
        $locale = 'en';
        $all = $this->getAll();

        return array_filter($all, function (PostConfiguration $postConfiguration) use ($category, $locale): ?PostConfiguration {
            if (!isset($postConfiguration->getLocale()[$locale])) {
                throw new PostConfigurationException('Unfortunately no posts found for this locale.');
            }

            $categoryInCurrentLocale = $postConfiguration->getLocale()[$locale]->getCategories();


            if (array_intersect($category, $categoryInCurrentLocale)) {
                return $postConfiguration;
            }
            return null;
        });
    }

    /**
     * // ToDo: Only english locale works at the moment when calling the method directly
     * @inheritDoc
     */
    public function getAllByTag(string ...$tag): array
    {
        // ToDo: Maybe also find a way to override the locale with a function parameter
        //  Since splat operator is used, no optional parameters can be passed
        //  Like this the function ain't that flexible

        // ToDo: LocaleService should be injected into the constructor and used as class property
        //  $this->locale = $localeService->getLocale();
        $locale = 'en';
        $all = $this->getAll();

        return array_filter($all, function (PostConfiguration $postConfiguration) use ($tag, $locale): ?PostConfiguration {
            if (!isset($postConfiguration->getLocale()[$locale])) {
                throw new PostConfigurationException('Unfortunately no posts found for this locale.');
            }

            $tagsInCurrentLocale = $postConfiguration->getLocale()[$locale]->getTags();

            if (array_intersect($tag, $tagsInCurrentLocale)) {
                return $postConfiguration;
            }
            return null;
        });
    }

    /**
     * Gets the Path for the folder containing all blog entries
     * @return string
     */
    private function getPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            PostConfigurationService::PATH_POSTS,
        ]);
    }


    /**
     * Gets the Path for the given blog post
     * @param string $entry
     * @return string
     */
    private function getPostPath(string $entry): string
    {
        $path = $this->getPath();

        return Path::join(...[
            $path,
            $entry,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getConfigurationPath(string $entry): string
    {
        $path = $this->getPostPath($entry);

        return Path::join(...[
            $path,
            PostConfigurationService::PATH_CONFIGURATION,
        ]);
    }

    /**
     * @param string $path
     * @return array
     */
    private function getContent(string $path): array
    {
        $content = File::read($path);
        return Json::decode($content);
    }
}
