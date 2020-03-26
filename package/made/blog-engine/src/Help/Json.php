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

namespace Made\Blog\Engine\Help;

/**
 * Class Json
 *
 * TODO: Move static helper classes to package gameplayjdk/static-help.
 *
 * @package Help
 */
final class Json
{
    /**
     * Encode an array to a json string naively. That means, the php and json type are assumed.
     *
     * @param array $var
     * @param bool $pretty
     * @return string
     */
    public static function encode(array $var, bool $pretty = false): string
    {
        $result = json_encode($var, ($pretty ? JSON_PRETTY_PRINT : 0));

        if (false === $result || !is_string($result)) {
            $result = '';
        }

        return $result;
    }

    /**
     * Decode a json string to an array naively. That means, the php and json type are assumed.
     *
     * @param string $var
     * @return array
     */
    public static function decode(string $var): array
    {
        $result = json_decode($var, true);

        if (null === $result || !is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * @return int
     */
    public static function getError(): int
    {
        return json_last_error();
    }

    /**
     * @return string
     */
    public static function getErrorMessage(): string
    {
        return json_last_error_msg() ?: '';
    }
}
