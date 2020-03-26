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

use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\Criteria\Criteria;

interface PostConfigurationRepositoryInterface
{
    const TAG_POST_CONFIGURATION_REPOSITORY = 'repository.post_configuration';

    /**
     * @param PostConfiguration $postConfiguration
     * @return bool
     */
    public function create(PostConfiguration $postConfiguration): bool;

    /**
     * @param Criteria $criteria
     * @return array|PostConfiguration[]
     */
    public function getAll(Criteria $criteria): array;

    /**
     * @param string $id
     * @return PostConfiguration|null
     */
    public function getOneById(string $id): ?PostConfiguration;

    /**
     * @param PostConfiguration $postConfiguration
     * @return bool
     */
    public function modify(PostConfiguration $postConfiguration): bool;

    /**
     * @param PostConfiguration $postConfiguration
     * @return bool
     */
    public function destroy(PostConfiguration $postConfiguration): bool;
}
