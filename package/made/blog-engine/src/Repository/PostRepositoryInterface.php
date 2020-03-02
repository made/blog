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

use DateTime;
use Made\Blog\Engine\Model\Post;

/**
 * Interface PostRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface PostRepositoryInterface
{
    const TAG_POST_REPOSITORY = 'repository.post';

    /**
     * @return array|Post[]
     */
    public function getAll(): array;

    /**
     * @param DateTime $dateTime
     * @return array|Post[]
     */
    public function getAllByPostDate(DateTime $dateTime): array;

    /**
     * @param string ...$statusList
     * @return array|Post[]
     */
    public function getAllByStatus(string ...$statusList): array;

    /**
     * @param string ...$categoryList
     * @return array|Post[]
     */
    public function getAllByCategory(string ...$categoryList): array;

    /**
     * @param string ...$tagList
     * @return array|Post[]
     */
    public function getAllByTag(string ...$tagList): array;

    /**
     * @param string $id
     * @return Post|null
     */
    public function getOneById(string $id): ?Post;

    /**
     * @param string $slug
     * @return Post|null
     */
    public function getOneBySlug(string $slug): ?Post;

    /**
     * @param string $slugRedirect
     * @return Post|null
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?Post;
}
