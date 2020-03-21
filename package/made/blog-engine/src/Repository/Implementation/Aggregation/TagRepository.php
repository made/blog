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
use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\TagRepositoryInterface;

/**
 * Class TagRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class TagRepository implements TagRepositoryInterface
{
    /**
     * @var array|TagRepositoryInterface[]
     */
    private $tagRepositoryList;

    /**
     * TagRepository constructor.
     * @param array|TagRepositoryInterface[] $tagRepositoryList
     */
    public function __construct(array $tagRepositoryList)
    {
        $this->tagRepositoryList = $tagRepositoryList;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Tag $tag): bool
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

        foreach ($this->tagRepositoryList as $tagRepository) {
            array_push($all, ...$tagRepository->getAll($criteria));
        }

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?Tag
    {
        $one = null;

        foreach ($this->tagRepositoryList as $categoryRepository) {
            if (null !== ($one = $categoryRepository->getOneById($id))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Tag
    {
        $one = null;

        foreach ($this->tagRepositoryList as $categoryRepository) {
            if (null !== ($one = $categoryRepository->getOneByName($name))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(Tag $tag): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Tag $tag): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }
}
