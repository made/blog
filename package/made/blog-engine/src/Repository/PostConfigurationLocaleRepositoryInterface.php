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
use Made\Blog\Engine\Model\PostConfigurationLocale;

/**
 * Interface PostConfigurationLocaleRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface PostConfigurationLocaleRepositoryInterface
{
    const TAG_POST_CONFIGURATION_LOCALE_REPOSITORY = 'repository.post_configuration_locale';

    /**
     * @return array|\Made\Blog\Engine\Model\PostConfigurationLocale[]
     */
    public function getAll(): array;

    /**
     * @param DateTime $dateTime
     * @return array|\Made\Blog\Engine\Model\PostConfigurationLocale[]
     */
    public function getAllByPostDate(DateTime $dateTime): array;

    /**
     * @param string ...$statusList
     * @return array|\Made\Blog\Engine\Model\PostConfigurationLocale[]
     */
    public function getAllByStatus(string ...$statusList): array;

    /**
     * @param string ...$categoryList
     * @return array|\Made\Blog\Engine\Model\PostConfigurationLocale[]
     */
    public function getAllByCategory(string ...$categoryList): array;

    /**
     * @param string ...$tagList
     * @return array|\Made\Blog\Engine\Model\PostConfigurationLocale[]
     */
    public function getAllByTag(string ...$tagList): array;

    /**
     * @param string $id
     * @return \Made\Blog\Engine\Model\PostConfigurationLocale|null
     */
    public function getOneById(string $id): ?PostConfigurationLocale;

    /**
     * @param string $slug
     * @return \Made\Blog\Engine\Model\PostConfigurationLocale|null
     */
    public function getOneBySlug(string $slug): ?PostConfigurationLocale;

    /**
     * @param string $slugRedirect
     * @return \Made\Blog\Engine\Model\PostConfigurationLocale|null
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?PostConfigurationLocale;
}
