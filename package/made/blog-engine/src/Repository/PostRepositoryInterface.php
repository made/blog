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
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;

/**
 * Interface PostRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface PostRepositoryInterface
{
    const TAG_POST_REPOSITORY = 'repository.post';

    /**
     * @param Post $post
     * @return bool
     */
    public function create(Post $post): bool;

    /**
     * @param CriteriaLocale $criteria
     * @return array|Post[]
     */
    public function getAll(CriteriaLocale $criteria): array;

    /**
     * @param CriteriaLocale $criteria
     * @param DateTime $dateTime
     * @return array|Post[]
     */
    public function getAllByPostDate(CriteriaLocale $criteria, DateTime $dateTime): array;

    /**
     * @param CriteriaLocale $criteria
     * @param string ...$statusList
     * @return array|Post[]
     */
    public function getAllByStatus(CriteriaLocale $criteria, string ...$statusList): array;

    /**
     * @param CriteriaLocale $criteria
     * @param string ...$categoryList
     * @return array|Post[]
     */
    public function getAllByCategory(CriteriaLocale $criteria, string ...$categoryList): array;

    /**
     * @param CriteriaLocale $criteria
     * @param string ...$tagList
     * @return array|Post[]
     */
    public function getAllByTag(CriteriaLocale $criteria, string ...$tagList): array;

    /**
     * @param string $locale
     * @param string $id
     * @return Post|null
     */
    public function getOneById(string $locale, string $id): ?Post;

    /**
     * @param string $locale
     * @param string $slug
     * @return Post|null
     */
    public function getOneBySlug(string $locale, string $slug): ?Post;

    /**
     * @param string $locale
     * @param string $slugRedirect
     * @return Post|null
     */
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?Post;

    /**
     * @param Post $post
     * @return bool
     */
    public function modify(Post $post): bool;

    /**
     * @param Post $post
     * @return bool
     */
    public function destroy(Post $post): bool;
}
