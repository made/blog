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

use Closure;
use DateTime;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Utility\ClosureInspection\ClosureInspection;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * Class PostConfigurationLocaleRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class PostConfigurationLocaleRepository implements PostConfigurationLocaleRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostConfigurationLocaleRepository constructor.
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(PostConfigurationRepositoryInterface $postConfigurationRepository, LoggerInterface $logger)
    {
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(CriteriaLocale $criteria): array
    {
        $all = $this->postConfigurationRepository
            ->getAll($criteria);

        $locale = $criteria->getLocale();

        $allLocale = $this->convertToPostConfigurationLocale($locale, $all);

        return $this->applyCriteria($criteria, $allLocale, PostConfigurationLocale::class);
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(CriteriaLocale $criteria, DateTime $dateTime): array
    {
        $allLocale = $this->getAll($criteria);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($dateTime): bool {
            $oneLocaleDate = $oneLocale->getDate();

            return $oneLocaleDate->format(PostConfigurationLocaleMapper::DTS_FORMAT)
                === $dateTime->format(PostConfigurationLocaleMapper::DTS_FORMAT);
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(CriteriaLocale $criteria, string ...$statusList): array
    {
        $allLocale = $this->getAll($criteria);

        $statusList = array_change_key_case($statusList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($statusList): bool {
            $oneLocaleStatus = strtolower($oneLocale->getStatus());

            return in_array($oneLocaleStatus, $statusList, true);
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(CriteriaLocale $criteria, string ...$categoryList): array
    {
        $allLocale = $this->getAll($criteria);

        $categoryList = array_change_key_case($categoryList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($categoryList): bool {
            $oneLocaleCategoryList = array_change_key_case($oneLocale->getCategoryList(), CASE_LOWER);

            return !empty(array_intersect($oneLocaleCategoryList, $categoryList));
        });
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(CriteriaLocale $criteria, string ...$tagList): array
    {
        $allLocale = $this->getAll($criteria);

        $tagList = array_change_key_case($tagList, CASE_LOWER);

        return array_filter($allLocale, function (PostConfigurationLocale $oneLocale) use ($tagList): bool {
            $oneLocaleTagList = array_change_key_case($oneLocale->getTagList(), CASE_LOWER);

            return !empty(array_intersect($oneLocaleTagList, $tagList));
        });
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $locale, string $id): ?PostConfigurationLocale
    {
        $one = $this->postConfigurationRepository->getOneById($id);

        $allLocale = $this->convertToPostConfigurationLocale($locale, [
            $one,
        ]);

        return reset($allLocale) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $locale, string $slug): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll(new CriteriaLocale($locale));

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slug): ?PostConfigurationLocale {
            if (null === $carry && $this->compareSlug($slug, $oneLocale->getSlug())) {
                return $oneLocale;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll(new CriteriaLocale($locale));

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slugRedirect): ?PostConfigurationLocale {
            if (null === $carry && $this->compareSlug($slugRedirect, ...$oneLocale->getSlugRedirectList())) {
                return $oneLocale;
            }

            return $carry;
        }, null);
    }

    /**
     * @param string $locale
     * @param array|PostConfiguration[] $all
     * @return array|PostConfigurationLocale[]
     */
    private function convertToPostConfigurationLocale(string $locale, array $all): array
    {
        /** @var array|PostConfigurationLocale[] $allLocale */
        $allLocale = array_map(function (?PostConfiguration $postConfiguration) use ($locale): ?PostConfigurationLocale {
            if (null !== $postConfiguration) {
                $allLocale = $postConfiguration->getLocaleList();

                foreach ($allLocale as $oneLocale) {
                    if ($oneLocale->getLocale() !== $locale) {
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

    /**
     * @param string $slugSearch
     * @param string ...$slugList
     * @return string
     */
    private function compareSlug(string $slugSearch, string ...$slugList): string
    {
        $slugSearch = $this->cleanSlug($slugSearch);

        return array_reduce($slugList, function (bool $carry, string $slug) use ($slugSearch): bool {
            return $carry || $this->cleanSlug($slug) === $slugSearch;
        }, false);
    }

    /**
     * @param string $slugSearch
     * @return string
     */
    private function cleanSlug(string $slugSearch): string
    {
        $slugSearch = preg_replace('/\/{2,}/', '/', $slugSearch);
        $slugSearch = trim($slugSearch, '/');

        return "/$slugSearch";
    }
}
