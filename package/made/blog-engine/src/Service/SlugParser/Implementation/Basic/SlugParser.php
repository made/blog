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

namespace Made\Blog\Engine\Service\SlugParser\Implementation\Basic;

use Made\Blog\Engine\Service\SlugParserInterface;

/**
 * Class SlugParser
 *
 * @package Made\Blog\Engine\Service
 */
class SlugParser implements SlugParserInterface
{
    /**
     * Basic format supports:
     * - `en/some-slug-of-a-post`
     * - `/en/some-slug-of-a-post`
     * - `en/some-slug-of-a-post/`
     * - `/en/some-slug-of-a-post/`
     * - `/en/some-slug-of-a-post/asdf/1234/`
     * - `/en/some-slug-of-a-post/asdf/1234`
     * - `en/some-slug-of-a-post/1234_`
     * - `/en/some-slug-of-a-post/1234_`
     *
     * Basic format does not support:
     * - `/en/some-slug-of-a-post-`
     * - `/some-slug-of-a-post`
     *
     * {@see https://regex101.com/r/WvXbIE/2}
     */
    const PATTERN = '/^\/?([a-z]{2})\/([\w\-\/]+\w)\/?$/';

    /**
     * @inheritDoc
     */
    public function parse(string $slug): array
    {
        $identifier = [
            static::MATCH_FULL,
            static::MATCH_LOCALE,
            static::MATCH_SLUG,
        ];
        $match = [];

        $result = preg_match(static::PATTERN, $slug, $match);

        if (0 === $result || false === $result) {
            $match = array_fill(0, count($identifier), null);
        }

        return array_combine($identifier, $match) ?: [];
    }
}
