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

use Closure;

/**
 * Class Directory
 *
 * TODO: Move static helper classes to package gameplayjdk/static-help.
 *
 * @package Made\Blog\Engine\Help
 */
final class Directory
{
    /**
     * List content of a directory naively.
     *
     * @param string $path
     * @return array|string[]
     */
    public static function list(string $path): array
    {
        $content = [];
        $handle = @opendir($path);

        if ($handle !== false) {
            while (($entry = readdir($handle)) !== false) {
                $content[] = $entry;
            }

            closedir($handle);
        }

        return $content;
    }

    /**
     * List content of a directory naively. Filter entries not matching a given pattern.
     *
     * @param string $path
     * @param string $pattern
     * @return array|string[]
     */
    public static function listPattern(string $path, string $pattern = ''): array
    {
        $content = static::list($path);

        if (empty($content) || empty($pattern)) {
            return $content;
        }

        return array_filter($content, function (string $entry) use ($pattern): bool {
            return (bool)preg_match($pattern, $entry);
        });
    }

    /**
     * List content of a directory naively. Filter entries returning a non-falsy value from the callback.
     *
     * @param string $path
     * @param Closure|null $callback
     * @return array|string[]
     */
    public static function listCallback(string $path, ?Closure $callback = null): array
    {
        $content = static::list($path);

        if (empty($content) || null === $callback) {
            return $content;
        }

        return array_filter($content, $callback);
    }
}
