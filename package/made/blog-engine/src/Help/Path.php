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
 * Class Path
 *
 * TODO: Move static helper classes to package gameplayjdk/static-help.
 *
 * @package Made\Blog\Engine\Help
 */
final class Path
{
    /**
     * Join an arbitrary amount of path segments. The regex used is \/{2,}.
     *
     * @param string ...$paths
     * @return string
     */
    public static function join(string ...$paths): string
    {
        return preg_replace('/\/{2,}/', '/', implode('/', $paths));
    }

    /**
     * Remove all dot segments from a path.
     *
     * Credit to https://stackoverflow.com/a/21424232 - as per RFC 3986:
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param string $input
     * @return bool|string
     * @deprecated Currently not in use.
     */
    public static function removeDotSegments(string $input): string
    {
        // 1.   The input buffer is initialized with the now-appended path
        //      components and the output buffer is initialized to the empty
        //      string.
        $output = '';

        // 2.   While the input buffer is not empty, loop as follows:
        while ($input !== '') {
            // A.   If the input buffer begins with a prefix of "`../`" or "`./`",
            //      then remove that prefix from the input buffer; otherwise,
            if (
                ($prefix = substr($input, 0, 3)) == '../' ||
                ($prefix = substr($input, 0, 2)) == './'
            ) {
                $input = substr($input, strlen($prefix));
            } else
                // B.   if the input buffer begins with a prefix of "`/./`" or "`/.`",
                //      where "`.`" is a complete path segment, then replace that
                //      prefix with "`/`" in the input buffer; otherwise,
                if (
                    ($prefix = substr($input, 0, 3)) == '/./' ||
                    ($prefix = $input) == '/.'
                ) {
                    $input = '/' . substr($input, strlen($prefix));
                } else
                    // C.   if the input buffer begins with a prefix of "/../" or "/..",
                    //      where "`..`" is a complete path segment, then replace that
                    //      prefix with "`/`" in the input buffer and remove the last
                    //      segment and its preceding "/" (if any) from the output
                    //      buffer; otherwise,
                    if (
                        ($prefix = substr($input, 0, 4)) == '/../' ||
                        ($prefix = $input) == '/..'
                    ) {
                        $input = '/' . substr($input, strlen($prefix));
                        $output = substr($output, 0, strrpos($output, '/'));
                    } else
                        // D.   if the input buffer consists only of "." or "..", then remove
                        //      that from the input buffer; otherwise,
                        if ($input == '.' || $input == '..') {
                            $input = '';
                        } else
                            // E.   move the first path segment in the input buffer to the end of
                            //      the output buffer, including the initial "/" character (if
                            //      any) and any subsequent characters up to, but not including,
                            //      the next "/" character or the end of the input buffer.
                        {
                            $pos = strpos($input, '/');
                            if ($pos === 0) $pos = strpos($input, '/', $pos + 1);
                            if ($pos === false) $pos = strlen($input);
                            $output .= substr($input, 0, $pos);
                            $input = (string)substr($input, $pos);
                        }
        }

        // 3.   Finally, the output buffer is returned as the result of removeDotSegments().
        return $output;
    }
}
