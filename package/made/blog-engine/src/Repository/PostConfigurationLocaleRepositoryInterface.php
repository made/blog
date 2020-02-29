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
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationLocale;

/**
 * Interface PostConfigurationLocaleRepositoryInterface
 *
 * @package Made\Blog\Engine\Repository
 */
interface PostConfigurationLocaleRepositoryInterface
{
    const TAG_POST_CONFIGURATION_REPOSITORY = 'repository.post_configuration_locale';

    /**
     * @return array|PostConfigurationLocale[]
     */
    public function getAll(): array;

    /**
     * @param DateTime $dateTime
     * @return array|PostConfigurationLocale[]
     */
    public function getAllByPostDate(DateTime $dateTime): array;

    /**
     * @param string ...$status
     * @return array|PostConfigurationLocale[]
     */
    public function getAllByStatus(string ...$status): array;

    /**
     * @param string ...$category
     * @return array|PostConfigurationLocale[]
     */
    public function getAllByCategory(string ...$category): array;

    /**
     * @param string ...$tag
     * @return array|PostConfigurationLocale[]
     */
    public function getAllByTag(string ...$tag): array;

    /**
     * @param string $id
     * @return PostConfigurationLocale|null
     */
    public function getOneById(string $id): ?PostConfigurationLocale;

    /**
     * @param string $slug
     * @return PostConfigurationLocale|null
     */
    public function getOneBySlug(string $slug): ?PostConfigurationLocale;

    /**
     * @param string $slugRedirect
     * @return PostConfigurationLocale|null
     */
    public function getOneBySlugRedirect(string $slugRedirect): ?PostConfigurationLocale;
}