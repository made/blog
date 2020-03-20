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

namespace Made\Blog\Engine\Repository;

use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\Criteria\Criteria;

/**
 * Interface TagRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface TagRepositoryInterface
{
    const TAG_TAG_REPOSITORY = 'repository.tag';

    /**
     * @param Criteria $criteria
     * @return array|Tag[]
     */
    public function getAll(Criteria $criteria): array;

    /**
     * @param string $id
     * @return Tag|null
     */
    public function getOneById(string $id): ?Tag;

    /**
     * @param string $name
     * @return Tag|null
     */
    public function getOneByName(string $name): ?Tag;
}
