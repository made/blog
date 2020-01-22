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
use Made\Blog\Engine\Repository\PostConfigurationFileRepositoryInterface;
use Psr\Log\LoggerInterface;

class PostConfigurationLocaleRepository implements PostConfigurationFileRepositoryInterface
{
    // ToDo: Idea -> If there are only 'en' entries in the locale of all blog posts, then the slug should not contain the
    //  locale (ex. /en/how-to-x would be /how-to-x)
    //  Like this the configuration stays the same for non-multilingual and multilingual sites.
    //  Always use the PostConfigurationLocaleRepository, since the default locale is always given to this repository.

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
     * @inheritDoc
     */
    public function getOneBySlug(string $name): ?PostConfiguration
    {
        // Slug always needs a locale
        return $this->postConfigurationRepository->getOneBySlug($name);
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $name): ?PostConfiguration
    {
        // Slug Redirect always needs a locale
        return $this->postConfigurationRepository->getOneBySlugRedirect($name);
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$category): array
    {
        // Category always needs a locale
        return $this->postConfigurationRepository->getAllByCategory($category);
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tag): array
    {
        // Tags always needs a locale
        return $this->postConfigurationRepository->getAllByTag($tag);
    }
}
