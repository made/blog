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

use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Made\Blog\Engine\Model\Author;
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;

/**
 * Class AuthorRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * @var array|AuthorRepositoryInterface[]
     */
    private $authorRepositoryList;

    /**
     * AuthorRepository constructor.
     * @param array|AuthorRepositoryInterface[] $authorRepositoryList
     */
    public function __construct($authorRepositoryList)
    {
        $this->authorRepositoryList = $authorRepositoryList;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $all = [];

        foreach ($this->authorRepositoryList as $authorRepository) {
            array_push($all, ...$authorRepository->getAll($criteria));
        }

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getAllByLocation(Criteria $criteria, string $location): array
    {
        $all = [];

        foreach ($this->authorRepositoryList as $authorRepository) {
            array_push($all, ...$authorRepository->getAllByLocation($criteria, $location));
        }

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Author
    {
        $one = null;

        foreach ($this->authorRepositoryList as $authorRepository) {
            if (null !== ($one = $authorRepository->getOneByName($name))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     */
    public function getOneByNameDisplay(string $nameDisplay): ?Author
    {
        $one = null;

        foreach ($this->authorRepositoryList as $authorRepository) {
            if (null !== ($one = $authorRepository->getOneByNameDisplay($nameDisplay))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }
}
