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

use DateTime;
use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PostConfigurationLocaleRepository
 *
 * @TODO Formatting is really messed up... please fix it.
 * @TODO Tidy up the docblocks, use more inheritdoc and put the docblocks into the interfaces, where they belong.
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class PostConfigurationLocaleRepository implements PostConfigurationLocaleRepositoryInterface
{
    // info: Idea -> If there are only 'en' entries in the locale of all blog posts, then the slug should not contain the
    //  locale (ex. /en/how-to-x would be /how-to-x)
    //  Like this the configuration stays the same for non-multilingual and multilingual sites.
    //  Always use the PostConfigurationLocaleRepository, since the default locale is always given to this repository.

    /**
     * @var PostConfigurationRepository
     */
    private $postConfigurationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $currentLocale;

    /**
     * PostConfigurationLocaleRepository constructor.
     * @param PostConfigurationRepository $postConfigurationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(PostConfigurationRepository $postConfigurationRepository, LoggerInterface $logger)
    {
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->logger = $logger;

        // TODO: Find some good place to find.
        $this->currentLocale = 'en';
    }

    /**
     * @return array|PostConfigurationLocale[]
     */
    public function getAll(): array
    {
        $all = $this->postConfigurationRepository->getAll();

        /** @var array|PostConfigurationLocale[] $allLocale */
        $allLocale = array_map(function (PostConfiguration $one): array {
            $allLocale = $one->getLocale();

            foreach ($allLocale as $oneLocale) {
                if ($oneLocale->getLocale() !== $this->getCurrentLocale()) {
                    continue;
                }

                return $oneLocale;
            }

            return null;
        }, $all);

        return array_filter($allLocale, function (?PostConfigurationLocale $oneLocale): bool {
            return null !== $oneLocale;
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(DateTime $dateTime): array
    {
        $allLocale = $this->getAll();

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($dateTime): bool {
            $postConfigurationDate = $oneLocale->getDate();

            return $postConfigurationDate->format(PostConfigurationLocaleMapper::DTS_FORMAT)
                === $dateTime->format(PostConfigurationLocaleMapper::DTS_FORMAT);
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(string ...$status): array
    {
        $allLocale = $this->getAll();

        $status = array_map(function (string $status): string {
            return strtolower($status);
        }, $status);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($status): bool {
            $postConfigurationStatus = strtolower($oneLocale->getStatus());

            return in_array($postConfigurationStatus, $status, true);
        });
    }

    /**
     * @TODO
     * @param string ...$category
     * @return array
     */
    public function getAllByCategory(string ...$category): array
    {
        return [];
    }

    /**
     * @TODO
     * @param string ...$tag
     * @return array
     */
    public function getAllByTag(string ...$tag): array
    {
        return [];
    }

    /**
     * @param string $id
     * @return PostConfigurationLocale|null
     */
    public function getOneById(string $id): ?PostConfigurationLocale
    {
        // Could use the getOneById() of the PostConfigurationRepository...

        $allLocale = $this->getAll();

        return array_reduce($allLocale,
            function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($id
            ): ?PostConfigurationLocale {
                if (null === $carry && strtolower($id) === strtolower($oneLocale->getId())) {
                    return $oneLocale;
                }

                return $carry;
            }, null);
    }

    /**
     * @TODO Slug comparison should be improved
     * @param string $slug
     * @return PostConfigurationLocale|null
     */
    public function getOneBySlug(string $slug): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll();

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slug): ?PostConfigurationLocale {
            if (null === $carry && $slug === $oneLocale->getSlug()) {
                return $oneLocale;
            }

            return $carry;
        }, null);
    }

    /**
     * @TODO Slug comparison should be improved
     * @param string $slugRedirect
     * @return PostConfigurationLocale|null
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll();

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slugRedirect): ?PostConfigurationLocale {
            if (null === $carry && in_array($slugRedirect, $oneLocale->getSlugRedirects(), true)) {
                return $oneLocale;
            }

            return $carry;
        }, null);
    }

    /**
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * @param string $currentLocale
     * @return PostConfigurationLocaleRepository
     */
    public function setCurrentLocale(string $currentLocale): PostConfigurationLocaleRepository
    {
        $this->currentLocale = $currentLocale;
        return $this;
    }
}
