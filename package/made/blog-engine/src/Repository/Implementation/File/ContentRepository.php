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

use Made\Blog\Engine\Exception\ContentException;
use Made\Blog\Engine\Help\Directory;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Content\Content;
use Made\Blog\Engine\Repository\ContentFileRepositoryInterface;
use Made\Blog\Engine\Repository\Mapper\ContentMapper;
use Made\Blog\Engine\Service\ContentService;
use Psr\Log\LoggerInterface;

class ContentRepository implements ContentFileRepositoryInterface
{
    // ToDo: array_column to get a summary of the categories and tags of all posts :)
    //  for Methods like getAllCategories() or getAllTags()
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ContentMapper
     */
    private $contentMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContentRepository constructor.
     * @param Configuration $configuration
     * @param ContentMapper $contentMapper
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, ContentMapper $contentMapper, ?LoggerInterface $logger)
    {
        // ToDo: Inject the default locale
        $this->configuration = $configuration;
        $this->contentMapper = $contentMapper;
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

            $contentPath = $this->getContentPath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            $requiredFilesExist = is_dir($contentPath) && is_file($configurationPath);

            if (!$requiredFilesExist) {
                var_dump($contentPath);
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

        $all = array_map(function (string $entry): ?Content {
            $configurationPath = $this->getConfigurationPath($entry);

            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            try {
                return $this->contentMapper->fromData($data);
            } catch (ContentException $exception) {
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
    public function getOneBySlug(string $slug, string $locale = null): ?Content
    {
        if ($locale === null) {
            // ToDo: LocaleService should be injected into the constructor and used as class property
            //  $this->locale = $localeService->getLocale();
            $locale = 'en';
        }

        $all = $this->getAll();

        return array_reduce($all, function (?Content $carry, Content $content) use ($slug, $locale): ?Content {
            if (!isset($content->getLocale()[$locale])) {
                throw new ContentException('Unfortunately no content for this locale');
            }

            $slugInCurrentLocale = $content->getLocale()[$locale]->getSlug();

            if (null === $carry && $slugInCurrentLocale === $slug) {
                $carry = $content;
            }

            return $carry;
        }, null);
        // ToDo: Maybe think about also searching for redirect slugs if nothing is found above.
        //  Maybe an extra Repository Layer for this (Proxy).
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $slugRedirect, string $locale = null): ?Content
    {
        if ($locale === null) {
            // ToDo: LocaleService should be injected into the constructor and used as class property
            //  $this->locale = $localeService->getLocale();
            $locale = 'en';
        }

        $all = $this->getAll();

        return array_reduce($all, function (?Content $carry, Content $content) use ($slugRedirect, $locale): ?Content {
            if (!isset($content->getLocale()[$locale])) {
                throw new ContentException('Unfortunately no content for this locale');
            }

            $redirectInCurrentLocale = $content->getLocale()[$locale]->getRedirect();

            if (null === $carry && in_array($slugRedirect, $redirectInCurrentLocale)) {
                $carry = $content;
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

        return array_filter($all, function (Content $content) use ($category, $locale): ?Content {
            if (!isset($content->getLocale()[$locale])) {
                throw new ContentException('Unfortunately no content for this locale');
            }

            $categoryInCurrentLocale = $content->getLocale()[$locale]->getCategories();


            if (array_intersect($category, $categoryInCurrentLocale)) {
                return $content;
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

        return array_filter($all, function (Content $content) use ($tag, $locale): ?Content {
            if (!isset($content->getLocale()[$locale])) {
                throw new ContentException('Unfortunately no content for this locale');
            }

            $tagsInCurrentLocale = $content->getLocale()[$locale]->getTags();

            if (array_intersect($tag, $tagsInCurrentLocale)) {
                return $content;
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
            ContentService::PATH_CONTENT,
        ]);
    }


    /**
     * Gets the Path for the given blog entry
     * @param string $entry
     * @return string
     */
    private function getContentPath(string $entry): string
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
        $path = $this->getContentPath($entry);

        return Path::join(...[
            $path,
            ContentService::PATH_CONFIGURATION,
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
