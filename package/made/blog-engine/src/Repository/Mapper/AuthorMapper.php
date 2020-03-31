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

namespace Made\Blog\Engine\Repository\Mapper;

use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\Author;

/**
 * Class AuthorMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class AuthorMapper
{
    const KEY_NAME = 'name';
    const KEY_NAME_DISPLAY = 'name_display';
    const KEY_LOCATION = 'location';
    const KEY_TITLE = 'title';
    const KEY_PICTURE = 'picture';
    const KEY_DESCRIPTION = 'description';

    /**
     * @param array $data
     * @return Author
     * @throws FailedOperationException
     */
    public function fromData(array $data): Author
    {
        $author = new Author();

        // Required:
        if (isset($data[static::KEY_NAME]) && is_string($data[static::KEY_NAME])) {
            $author->setName($data[static::KEY_NAME]);
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_NAME);
        }

        // Optional:
        if (isset($data[static::KEY_NAME_DISPLAY]) && is_string($data[static::KEY_NAME_DISPLAY])) {
            $author->setNameDisplay($data[static::KEY_NAME_DISPLAY]);
        }

        // Optional:
        if (isset($data[static::KEY_LOCATION]) && is_string($data[static::KEY_LOCATION])) {
            $author->setLocation($data[static::KEY_LOCATION]);
        }

        // Optional:
        if (isset($data[static::KEY_TITLE]) && is_string($data[static::KEY_TITLE])) {
            $author->setTitle($data[static::KEY_TITLE]);
        }

        // Optional:
        if (isset($data[static::KEY_PICTURE]) && is_string($data[static::KEY_PICTURE])) {
            $author->setPicture($data[static::KEY_PICTURE]);
        }

        // Optional:
        if (isset($data[static::KEY_DESCRIPTION]) && is_string($data[static::KEY_DESCRIPTION])) {
            $author->setDescription($data[static::KEY_DESCRIPTION]);
        }

        return $author;
    }

    /**
     * @param Author $author
     * @return array
     */
    public function toData(Author $author): array
    {
        $data = [];

        $data[static::KEY_NAME] = $author->getName();
        $data[static::KEY_NAME_DISPLAY] = $author->getNameDisplay();
        $data[static::KEY_LOCATION] = $author->getLocation();
        $data[static::KEY_TITLE] = $author->getTitle();
        $data[static::KEY_PICTURE] = $author->getPicture();
        $data[static::KEY_DESCRIPTION] = $author->getDescription();

        return $data;
    }
}
