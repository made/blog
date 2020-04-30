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

namespace Made\Blog\Engine\Repository\Criteria\Factory;

use DateTime;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Criteria\Filter;

/**
 * Class FilterFactory
 *
 * @package Made\Blog\Engine\Repository\Criteria\Factory
 */
final class FilterFactory
{
    /**
     * @param DateTime $dateTime
     * @param string $key
     * @param string $format
     * @return Filter|null
     */
    public static function byDate(DateTime $dateTime, string $key, string $format = 'Y-m'): ?Filter
    {
        $key = static::normalizeKey($key);
        $methodName = __FUNCTION__ . $key;

        return method_exists(static::class, $methodName)
            ? static::$methodName($dateTime, $format)
            : null;
    }

    /**
     * @param DateTime $dateTime
     * @param string $format
     * @return Filter
     */
    public static function byDatePostConfigurationLocale(DateTime $dateTime, string $format = 'Y-m'): Filter
    {
        $callback = function (PostConfigurationLocale $postConfigurationLocale) use ($dateTime, $format): bool {
            $dateTimeLocale = $postConfigurationLocale->getDate();

            return $dateTimeLocale->format($format) === $dateTime->format($format);
        };

        return new Filter(__FUNCTION__, $callback);
    }

    /**
     * @param array $list
     * @param string $key
     * @return Filter
     */
    public static function byIdInList(array $list, string $key): ?Filter
    {
        $key = static::normalizeKey($key);
        $methodName = __FUNCTION__ . $key;

        return method_exists(static::class, $methodName)
            ? static::$methodName($list)
            : null;
    }

    /**
     * @param array $list
     * @return Filter
     */
    public static function byIdInListPostConfigurationLocale(array $list): Filter
    {
        $callback = function (PostConfigurationLocale $postConfigurationLocale) use ($list): bool {
            $idLocale = $postConfigurationLocale->getId();

            return in_array($idLocale, $list);
        };

        return new Filter(__FUNCTION__, $callback);
    }

    /**
     * @param string $key
     * @return string
     */
    public static function normalizeKey(string $key): string
    {
        $segment = explode('\\', $key);

        return end($segment) ?: $key;
    }
}
