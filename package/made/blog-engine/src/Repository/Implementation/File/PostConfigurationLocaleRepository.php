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
