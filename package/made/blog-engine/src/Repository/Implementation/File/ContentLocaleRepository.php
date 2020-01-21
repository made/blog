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
use Made\Blog\Engine\Model\Content\Content;
use Made\Blog\Engine\Repository\ContentFileRepositoryInterface;
use Psr\Log\LoggerInterface;

class ContentLocaleRepository implements ContentFileRepositoryInterface
{
    // ToDo: Idea -> Providing a possibility to enable/disable multilingual blogs.
    //  Config still needs the same format with its input in locale, but only the default language is used.
    //  If it's deactivated, then the URL routes does not have the locale ex. /en, /de, /pl, etc in it.
    //  If they are disabled, ContentRepository is ALWAYS used, if it's enabled, then ContentLocaleRepository.
    //  Otherwise this class makes no sense except using the getAll() function.
    //  Let us discuss this.

    /**
     * @var ContentRepository
     */
    private $contentRepository;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContentLocaleRepository constructor.
     * @param ContentRepository $contentRepository
     * @param LoggerInterface $logger
     */
    public function __construct(ContentRepository $contentRepository, ?LoggerInterface $logger)
    {
        $this->contentRepository = $contentRepository;
        $this->logger = $logger;
    }

    /**
     * @param string $locale
     * @return ContentLocaleRepository
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
        $all = $this->contentRepository->getAll();
        $locale = $this->locale;

        $contentCollection = array_filter($all, function (Content $content) use ($locale): ?Content {
            if (!isset($content->getLocale()[$locale])) {
                return null;
            }

            return $content;

        }, ARRAY_FILTER_USE_BOTH);

        if (empty($contentCollection)) {
            // ToDo: Logging
            throw new ContentException('Unfortunately no content was found for locale ' . $locale);
        }

        return array_values($contentCollection);
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $name): ?Content
    {
        // Slug always needs a locale
        return $this->contentRepository->getOneBySlug($name);
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $name): ?Content
    {
        // Slug Redirect always needs a locale
        return $this->contentRepository->getOneBySlugRedirect($name);
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$category): array
    {
        // Category always needs a locale
        return $this->contentRepository->getAllByCategory($category);
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tag): array
    {
        // Tags always needs a locale
        return $this->contentRepository->getAllByTag($tag);
    }
}
