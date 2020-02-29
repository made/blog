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

namespace Made\Blog\Engine\Util\Sorter;

use Made\Blog\Engine\Exception\PostException;
use Made\Blog\Engine\Model\PostConfiguration;

/**
 * Class PostConfigurationSorter
 *
 * @package Made\Blog\Engine\Util\Sorter
 * @TODO Refactor to enable usage on PostConfigurationLocale object array.
 * @deprecated Unable to use with the new model.
 */
class PostConfigurationSorter
{
    public const ORDER_DESC = 'DESC';
    public const ORDER_ASC = 'ASC';

    /**
     * @param array|PostConfiguration[] $all
     * @param string|null $order
     * @return array|PostConfiguration[]
     */
    public static function sortByPostDate(array $all, ?string $order = null): array
    {
        if (null === $order) {
            $order = static::ORDER_DESC;
        }

        usort($all, function (PostConfiguration $a, PostConfiguration $b) use ($order) {
            if ($a->getDate() === $b->getDate()) {
                return 0;
            }

            if ($order === static::ORDER_DESC) {
                return $a->getDate() > $b->getDate() ? -1 : 1;
            } else if ($order === static::ORDER_ASC) {
                return $a->getDate() < $b->getDate() ? -1 : 1;
            } else {
                throw new PostException("Order '$order' is not valid.");
            }
        });

        return $all;
    }
}
