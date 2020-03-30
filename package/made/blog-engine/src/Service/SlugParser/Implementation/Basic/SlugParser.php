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
     * - /
     * - /de
     * - /en
     * - /de/asdf/asdf/post-slug
     * - /de/asdf/asdf/post-slug/
     * - /en/asdf/asdf/as-df
     * - /en/asdf/asdf/as-df/
     * - /de/post-slug
     * - /en/post-slug
     * - /de/post
     * - /en/post
     * - /de/category
     * - /de/category/10
     * - /en/category
     * - /en/category/10
     * - /de/tag
     * - /de/tag/10
     * - /en/tag
     * - /en/tag/10
     *
     * Basic format does not support:
     * - /de/post-slug-
     * - /en/post-slug-
     * - /_asdf
     *
     * {@see https://regex101.com/r/WvXbIE/6}
     */
    const PATTERN = '/^\/([a-z]{2})(\/[\w\-\/]+\w)?\/?|\/$/';

    /**
     * @inheritDoc
     */
    public function parse(string $slug): array
    {
        // TODO: Put this mechanic into a 'Preg' static helper class. It is also used inside the page data provider.
        $identifier = [
            static::MATCH_FULL,
            static::MATCH_LOCALE,
            static::MATCH_SLUG,
        ];
        $match = [];

        preg_match(static::PATTERN, $slug, $match, PREG_UNMATCHED_AS_NULL);
        while (count($match) < count($identifier)) {
            $match[] = null;
        }

        return array_combine($identifier, $match) ?: [];
    }
}
