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
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Psr\Log\LoggerInterface;

class PostConfigurationLocaleRepository implements PostConfigurationRepositoryInterface
{
    // ToDo: Idea -> If there are only 'en' entries in the locale of all blog posts, then the slug should not contain the
    //  locale (ex. /en/how-to-x would be /how-to-x)
    //  Like this the configuration stays the same for non-multilingual and multilingual sites.
    //  Always use the PostConfigurationLocaleRepository, since the default locale is always given to this repository.

    // ToDo: array_column to get a summary of the categories and tags of all posts :)
    //  for Methods like getAllCategories() or getAllTags()
    /**
     * @var PostConfigurationRepository
     */
    private $postConfigurationRepository;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostConfigurationLocaleRepository constructor.
     * @param PostConfigurationRepository $postConfigurationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(PostConfigurationRepository $postConfigurationRepository, ?LoggerInterface $logger)
    {
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->logger = $logger;
    }

    /**
     * @param string $locale
     * @return PostConfigurationLocaleRepository
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $all = $this->postConfigurationRepository->getAll();
        $locale = $this->locale;

        $postConfigurationCollection = array_filter($all, function (PostConfiguration $postConfiguration) use ($locale): ?PostConfiguration {
            if (!isset($postConfiguration->getLocale()[$locale])) {
                return null;
            }

            return $postConfiguration;

        }, ARRAY_FILTER_USE_BOTH);

        if (empty($postConfigurationCollection)) {
            // ToDo: Logging
            throw new PostConfigurationException('Unfortunately no blog posts found for locale ' . $locale);
        }

        return array_values($postConfigurationCollection);
    }

    /**
     * Get any post configuration from slug name.
     * @param string $slug
     * @return PostConfiguration|null
     */
    public function getOneBySlug(string $slug): ?PostConfiguration
    {
        $locale = $this->locale;

        $all = $this->postConfigurationRepository->getAll();

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
    }

    /**
     * Get any redirect post configuration from slug name.
     * @param string $slugRedirect
     * @return PostConfiguration|null
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?PostConfiguration
    {
        $locale = $this->locale;

        $all = $this->postConfigurationRepository->getAll();

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
     * Get any redirect post configuration from one or more categories.
     * @param string ...$category
     * @return array|PostConfiguration[]
     */
    public function getAllByCategory(string ...$category): array
    {
        // ToDo: LocaleService should be injected into the constructor and used as class property
        //  $this->localeService->getLocale(); -> yes just do this.
        $locale = $this->locale;
        $all = $this->postConfigurationRepository->getAll();

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
     * Get any redirect post configuration from one or more tags.
     * @param string ...$tag
     * @return array|PostConfiguration[]
     */
    public function getAllByTag(string ...$tag): array
    {
        // ToDo: LocaleService should be injected into the constructor and used as class property
        //  $this->localeService->getLocale(); -> yes just do this.
        $locale = $this->locale;
        $all = $this->postConfigurationRepository->getAll();

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
}
