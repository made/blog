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
use Made\Blog\Engine\Model\Category;

/**
 * Class CategoryMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class CategoryMapper
{
    const KEY_ID = 'id';
    const KEY_NAME = 'name';

    /**
     * @param array $data
     * @return Category
     * @throws MapperException
     */
    public function fromData(array $data): Category
    {
        $category = new Category();

        // Required:
        if (isset($data[static::KEY_ID]) && is_string($data[static::KEY_ID])) {
            $category->setId($data[static::KEY_ID]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_ID);
        }

        // Required:
        if (isset($data[static::KEY_NAME]) && is_string($data[static::KEY_NAME])) {
            $category->setName($data[static::KEY_NAME]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_NAME);
        }

        return $category;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|Category[]
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
     * @param Category $category
     * @return array
     */
    public function toData(Category $category): array
    {
        $data = [];

        $data[static::KEY_ID] = $category->getId();
        $data[static::KEY_NAME] = $category->getName();

        return $data;
    }

    /**
     * @param array|Category[] $array
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
