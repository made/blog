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
     * @throws FailedOperationException
     */
    public function fromData(array $data): Theme
    {
        $theme = new Theme();

        // Required:
        if (isset($data[static::KEY_PATH]) && is_string($data[static::KEY_PATH])) {
            $theme->setPath($data[static::KEY_PATH]);
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_PATH);
        }

        // Required:
        if (isset($data[static::KEY_NAME]) && is_string($data[static::KEY_NAME])) {
            $theme->setName($data[static::KEY_NAME]);
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_NAME);
        }

        return $theme;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|Theme[]
     * @throws FailedOperationException
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
