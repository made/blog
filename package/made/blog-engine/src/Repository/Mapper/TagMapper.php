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

use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Model\Tag;

/**
 * Class TagMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class TagMapper
{
    const KEY_ID = 'id';
    const KEY_NAME = 'name';

    /**
     * @param array $data
     * @return Tag
     * @throws MapperException
     */
    public function fromData(array $data): Tag
    {
        $tag = new Tag();

        // Required:
        if (isset($data[static::KEY_ID]) && is_string($data[static::KEY_ID])) {
            $tag->setId($data[static::KEY_ID]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_ID);
        }

        // Required:
        if (isset($data[static::KEY_NAME]) && is_string($data[static::KEY_NAME])) {
            $tag->setName($data[static::KEY_NAME]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_NAME);
        }

        return $tag;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|Tag[]
     * @throws MapperException
     */
    public function fromDataArray(array $dataArray): array
    {
        $array = [];

        foreach ($dataArray as $data) {
            $array[] = $this->fromData($data);
        }

        return $array;
    }

    /**
     * @param Tag $tag
     * @return array
     */
    public function toData(Tag $tag): array
    {
        $data = [];

        $data[static::KEY_ID] = $tag->getId();
        $data[static::KEY_NAME] = $tag->getName();

        return $data;
    }

    /**
     * @param array|Tag[] $array
     * @return array|array[]
     */
    public function toDataArray(array $array): array
    {
        $dataArray = [];

        foreach ($array as $category) {
            $dataArray[] = $this->toData($category);
        }

        return $dataArray;
    }
}
