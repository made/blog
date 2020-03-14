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

/**
 * Class SlugParser
 *
 * @package Made\Blog\Engine\Service
 */
class SlugParser implements SlugParserInterface
{
    const PATTERN = '/^\/?([a-z]{2})\/([\w\-]+)\/?$/';

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
