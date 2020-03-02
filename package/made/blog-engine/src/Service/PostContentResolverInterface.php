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

namespace Made\Blog\Engine\Service;

use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\PostContent;

/**
 * Class PostContentResolverInterface
 *
 * @package Made\Blog\Engine\Service
 */
interface PostContentResolverInterface
{
    /**
     * @param PostConfigurationLocale $postConfigurationLocale
     * @return PostContent
     */
    public function resolve(PostConfigurationLocale $postConfigurationLocale): ?PostContent;
}
