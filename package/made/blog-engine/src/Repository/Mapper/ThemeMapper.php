<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Made
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Made\Blog\Engine\Repository\Mapper;

use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Model\Theme;

/**
 * Class ThemeMapper
 *
 * @package Made\Blog\Repository\Mapper
 */
class ThemeMapper
{
    const KEY_PATH = 'path';
    const KEY_NAME = 'name';

    /**
     * @param array $data
     * @return Theme
     * @throws MapperException
     */
    public function fromData(array $data): Theme
    {
        $theme = new Theme();

        // Required:
        if (isset($data[static::KEY_PATH]) && is_string($data[static::KEY_PATH])) {
            $theme->setPath($data[static::KEY_PATH]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_PATH);
        }

        // Required:
        if (isset($data[static::KEY_NAME]) && is_string($data[static::KEY_NAME])) {
            $theme->setName($data[static::KEY_NAME]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_NAME);
        }

        return $theme;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|Theme[]
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
     * @param Theme $theme
     * @return array
     */
    public function toData(Theme $theme): array
    {
        $data = [];

        $data[static::KEY_PATH] = $theme->getPath();
        $data[static::KEY_NAME] = $theme->getName();

        return $data;
    }

    /**
     * @param array|Theme[] $array
     * @return array|array[]
     */
    public function toDataArray(array $array): array
    {
        $dataArray = [];

        foreach ($array as $theme) {
            $dataArray[] = $this->toData($theme);
        }

        return $dataArray;
    }
}
