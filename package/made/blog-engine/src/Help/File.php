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
 * Class File
 *
 * TODO: Move static helper classes to package gameplayjdk/static-help.
 *
 * @package Made\Blog\Engine\Help
 */
final class File
{
    /**
     * Read a file naively. Chunk size is 1024.
     *
     * @param string $path
     * @return string
     */
    public static function read(string $path): string
    {
        $content = '';

        if (is_readable($path)) {
            $handle = @fopen($path, 'r');

            if (false !== $handle) {
                while (!feof($handle) && false !== ($chunk = fread($handle, 1024))) {
                    $content .= $chunk;
                }

                fclose($handle);
            }
        }

        return $content;
    }

    /**
     * Write a file naively. Chunk size is 1024.
     *
     * @param string $path
     * @param string $content
     * @deprecated This function is not yet tested.
     */
    public static function write(string $path, string $content): void
    {
        if (!file_exists($path) || (file_exists($path) && is_writable($path))) {
            $handle = @fopen($path, 'w');

            if (false !== $handle) {
                while (0 < strlen($content) && false !== ($chunk = fwrite($handle, $content, 1024))) {
                    $content = substr($content, $chunk) ?: '';
                }

                fclose($handle);
            }
        }
    }
}
