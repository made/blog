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

        if (!empty($allLocale)) {
            $allLocale = $this->filterBasedOnCriteria($criteria, $allLocale);
            $allLocale = $this->sliceBasedOnCriteria($criteria, $allLocale);
            $allLocale = $this->orderBasedOnCriteria($criteria, $allLocale);
        }

        return $allLocale;
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
     * @TODO Slug comparison should be improved.
     * @inheritDoc
     */
    public function getOneBySlug(string $locale, string $slug): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll(new CriteriaLocale($locale));

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
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?PostConfigurationLocale
    {
        $allLocale = $this->getAll(new CriteriaLocale($locale));

        return array_reduce($allLocale, function (?PostConfigurationLocale $carry, PostConfigurationLocale $oneLocale) use ($slugRedirect): ?PostConfigurationLocale {
            if (null === $carry && in_array($slugRedirect, $oneLocale->getSlugRedirectList(), true)) {
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

                    return $oneLocale
                        // This step is important for later usage, a PostConfigurationLocale must hold a reference to
                        // its PostConfiguration object.
                        ->setPostConfiguration($postConfiguration);
                }
            }

            return null;
        }, $all);

        return array_filter($allLocale, function (?PostConfigurationLocale $oneLocale): bool {
            return null !== $oneLocale;
        });
    }

    /**
     * @param CriteriaLocale $criteria
     * @param array|PostConfigurationLocale[] $allLocale
     * @return array|PostConfigurationLocale[]
     */
    private function filterBasedOnCriteria(CriteriaLocale $criteria, array $allLocale): array
    {
        if (null !== ($filter = $criteria->getFilter())) {
            /** @var Closure $callback */
            $callback = $filter->getCallback();
            /** @var ClosureInspection|null $callbackInspection */
            $callbackInspection = $this->createInspection($callback);

            if (null !== $callbackInspection && $callbackInspection->isParameterTypeClass(0, PostConfigurationLocale::class)) {
                $allLocale = array_filter($allLocale, $callback);
            }
        }

        return $allLocale;
    }

    /**
     * @param CriteriaLocale $criteria
     * @param array|PostConfigurationLocale[] $allLocale
     * @return array|PostConfigurationLocale[]
     */
    private function sliceBasedOnCriteria(CriteriaLocale $criteria, array $allLocale)
    {
        $offset = $criteria->getOffset();
        if (-1 === $offset) {
            $offset = null;
        }

        $limit = $criteria->getLimit();
        if (-1 === $limit) {
            $limit = null;
        }

        return array_slice($allLocale, $offset, $limit);
    }

    /**
     * @param CriteriaLocale $criteria
     * @param array|PostConfigurationLocale[] $allLocale
     * @return array|PostConfigurationLocale[]
     */
    private function orderBasedOnCriteria(CriteriaLocale $criteria, array $allLocale): array
    {
        if (null !== ($order = $criteria->getOrder())) {
            /** @var Closure $comparator */
            $comparator = $order->getComparator();
            /** @var ClosureInspection|null $comparatorInspection */
            $comparatorInspection = $this->createInspection($comparator);

            if (null !== $comparatorInspection
                && $comparatorInspection->isParameterTypeClass(0, PostConfigurationLocale::class)
                && $comparatorInspection->isParameterTypeClass(1, PostConfigurationLocale::class)) {
                usort($allLocale, $comparator);
            }
        }

        return $allLocale;
    }

    /**
     * @param Closure $callback
     * @return ClosureInspection|null
     */
    private function createInspection(Closure $callback): ?ClosureInspection
    {
        try {
            return ClosureInspection::on($callback);
        } catch (ReflectionException $exception) {
            // TODO: Logging.
        }

        return null;
    }
}
