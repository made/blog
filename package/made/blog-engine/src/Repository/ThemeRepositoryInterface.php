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

use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Criteria\Criteria;

/**
 * Interface ThemeRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface ThemeRepositoryInterface
{
    const TAG_THEME_REPOSITORY = 'repository.theme';

    /**
     * @param Criteria $criteria
     * @return array|Theme[]
     */
    public function getAll(Criteria $criteria): array;

    /**
     * @param string $name
     * @return Theme|null
     */
    public function getOneByName(string $name): ?Theme;
}
