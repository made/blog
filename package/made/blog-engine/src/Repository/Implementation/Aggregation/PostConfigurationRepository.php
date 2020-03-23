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
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;

/**
 * Class PostConfigurationRepository
 *
 * TODO: The aggregation layer is needed inside the PostRepository.
 *
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class PostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    /**
     * @var array|PostConfigurationRepositoryInterface[]
     */
    private $postConfigurationRepositoryList;

    /**
     * PostConfigurationRepository constructor.
     * @param array|PostConfigurationRepositoryInterface[] $postConfigurationRepositoryList
     */
    public function __construct(array $postConfigurationRepositoryList)
    {
        $this->postConfigurationRepositoryList = $postConfigurationRepositoryList;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(PostConfiguration $postConfiguration): bool
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

        foreach ($this->postConfigurationRepositoryList as $postConfigurationRepository) {
            array_push($all, ...$postConfigurationRepository->getAll($criteria));
        }

        // Non-unique entries will result in one of them being ignored.
        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?PostConfiguration
    {
        $one = null;

        foreach ($this->postConfigurationRepositoryList as $postConfigurationRepository) {
            if (null !== ($one = $postConfigurationRepository->getOneById($id))) {
                break;
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(PostConfiguration $postConfiguration): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(PostConfiguration $postConfiguration): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The aggregation repository can not be used for that type of action.');
    }
}
