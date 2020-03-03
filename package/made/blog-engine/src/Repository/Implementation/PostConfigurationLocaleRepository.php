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

namespace Made\Blog\Engine\Repository\Implementation;

use DateTime;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PostConfigurationLocaleRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation
 */
class PostConfigurationLocaleRepository implements PostConfigurationLocaleRepositoryInterface
{
    /**
     * @var PostConfigurationRepositoryInterface
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
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(PostConfigurationRepositoryInterface $postConfigurationRepository, LoggerInterface $logger)
    {
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->logger = $logger;

        $this->currentLocale = 'en';
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $all = $this->postConfigurationRepository->getAll();

        return $this->convertToPostConfigurationLocale($all);
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(DateTime $dateTime): array
    {
        $allLocale = $this->getAll();

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($dateTime): bool {
            $oneLocaleDate = $oneLocale->getDate();

            return $oneLocaleDate->format(PostConfigurationLocaleMapper::DTS_FORMAT)
                === $dateTime->format(PostConfigurationLocaleMapper::DTS_FORMAT);
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(string ...$statusList): array
    {
        $allLocale = $this->getAll();

        $statusList = array_change_key_case($statusList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($statusList): bool {
            $oneLocaleStatus = strtolower($oneLocale->getStatus());

            return in_array($oneLocaleStatus, $statusList, true);
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$categoryList): array
    {
        $allLocale = $this->getAll();

        $categoryList = array_change_key_case($categoryList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($categoryList): bool {
            $oneLocaleCategoryList = array_change_key_case($oneLocale->getCategoryList(), CASE_LOWER);

            return !empty(array_intersect($oneLocaleCategoryList, $categoryList));
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tagList): array
    {
        $allLocale = $this->getAll();

        $tagList = array_change_key_case($tagList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($tagList): bool {
            $oneLocaleTagList = array_change_key_case($oneLocale->getTagList(), CASE_LOWER);

            return !empty(array_intersect($oneLocaleTagList, $tagList));
        });
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?PostConfigurationLocale
    {
        $one = $this->postConfigurationRepository->getOneById($id);

        $allLocale = $this->convertToPostConfigurationLocale([
            $one,
        ]);

        return reset($allLocale) ?: null;
    }

    /**
     * @TODO Slug comparison should be improved.
     * @inheritDoc
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
     * @TODO Slug comparison should be improved.
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll();

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slugRedirect): ?PostConfigurationLocale {
            if (null === $carry && in_array($slugRedirect, $oneLocale->getSlugRedirectList(), true)) {
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

    /**
     * @param array|PostConfiguration[] $all
     * @return array|PostConfigurationLocale[]
     */
    private function convertToPostConfigurationLocale(array $all): array
    {
        /** @var array|PostConfigurationLocale[] $allLocale */
        $allLocale = array_map(function (?PostConfiguration $postConfiguration): ?PostConfigurationLocale {
            if (null !== $postConfiguration) {
                $allLocale = $postConfiguration->getLocaleList();

                foreach ($allLocale as $oneLocale) {
                    if ($oneLocale->getLocale() !== $this->getCurrentLocale()) {
                        continue;
                    }

                    return $oneLocale;
                }
            }

            return null;
        }, $all);

        return array_filter($allLocale, function (?PostConfigurationLocale $oneLocale): bool {
            return null !== $oneLocale;
        });
    }
}
