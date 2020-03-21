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

namespace Made\Blog\Engine\Repository\Implementation\Aggregation;

use DateTime;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;

/**
 * Class PostConfigurationLocaleRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class PostConfigurationLocaleRepository implements PostConfigurationLocaleRepositoryInterface
{
    /**
     * @var array|PostConfigurationLocaleRepository[]
     */
    private $postConfigurationLocaleRepositoryList;

    /**
     * PostConfigurationLocaleRepository constructor.
     * @param array|PostConfigurationLocaleRepository[] $postConfigurationLocaleRepositoryList
     */
    public function __construct(array $postConfigurationLocaleRepositoryList)
    {
        $this->postConfigurationLocaleRepositoryList = $postConfigurationLocaleRepositoryList;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(PostConfigurationLocale $postConfigurationLocale): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(CriteriaLocale $criteria): array
    {
        $allLocale = [];

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationLocaleRepository) {
            array_push($allLocale, ...$postConfigurationLocaleRepository->getAll($criteria));
        }

        return $allLocale;
    }

    /**
     * @inheritDoc
     */
    public function getAllByPostDate(CriteriaLocale $criteria, DateTime $dateTime): array
    {
        $allLocale = [];

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationLocaleRepository) {
            array_push($allLocale, ...$postConfigurationLocaleRepository->getAllByPostDate($criteria, $dateTime));
        }

        return $allLocale;
    }

    /**
     * @inheritDoc
     */
    public function getAllByStatus(CriteriaLocale $criteria, string ...$statusList): array
    {
        $allLocale = [];

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationLocaleRepository) {
            array_push($allLocale, ...$postConfigurationLocaleRepository->getAllByStatus($criteria, ...$statusList));
        }

        return $allLocale;
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(CriteriaLocale $criteria, string ...$categoryList): array
    {
        $allLocale = [];

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationLocaleRepository) {
            array_push($allLocale, ...$postConfigurationLocaleRepository->getAllByCategory($criteria, ...$categoryList));
        }

        return $allLocale;
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(CriteriaLocale $criteria, string ...$tagList): array
    {
        $allLocale = [];

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationLocaleRepository) {
            array_push($allLocale, ...$postConfigurationLocaleRepository->getAllByTag($criteria, ...$tagList));
        }

        return $allLocale;
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $locale, string $id): ?PostConfigurationLocale
    {
        $one = null;

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationRepository) {
            if (null !== ($one = $postConfigurationRepository->getOneById($locale, $id))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $locale, string $slug): ?PostConfigurationLocale
    {
        $one = null;

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationRepository) {
            if (null !== ($one = $postConfigurationRepository->getOneBySlug($locale, $slug))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?PostConfigurationLocale
    {
        $one = null;

        foreach ($this->postConfigurationLocaleRepositoryList as $postConfigurationRepository) {
            if (null !== ($one = $postConfigurationRepository->getOneBySlugRedirect($locale, $slugRedirect))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(PostConfigurationLocale $postConfigurationLocale): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(PostConfigurationLocale $postConfigurationLocale): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }
}
