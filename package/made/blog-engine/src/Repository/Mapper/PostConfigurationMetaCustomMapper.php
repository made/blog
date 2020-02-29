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
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationMetaCustom;

/**
 * Class PostConfigurationMetaCustomMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationMetaCustomMapper
{
    use NormalizeValueArrayTrait;

    const KEY_ELEMENT = 'element';
    const KEY_ATTRIBUTES = 'attributes';

    /**
     * @param array $data
     * @return PostConfigurationMetaCustom
     * @throws MapperException
     */
    public function fromData(array $data): PostConfigurationMetaCustom
    {
        $postConfigurationMetaCustom = new PostConfigurationMetaCustom();

        // Required:
        if (isset($data[static::KEY_ELEMENT]) && is_string($data[static::KEY_ELEMENT])) {
            $postConfigurationMetaCustom->setElement($data[static::KEY_ELEMENT]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_ELEMENT);
        }

        // Optional:
        if (isset($data[static::KEY_ATTRIBUTES]) && is_array($data[static::KEY_ATTRIBUTES]) && !empty($attributes = $this->normalizeValueArray($data[static::KEY_ATTRIBUTES], false))) {
            $postConfigurationMetaCustom->setAttributes($attributes);
        }

        return $postConfigurationMetaCustom;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|PostConfigurationMetaCustom[]
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
     * @param PostConfigurationMetaCustom $postConfigurationMetaCustom
     * @return array
     */
    public function toData(PostConfigurationMetaCustom $postConfigurationMetaCustom): array
    {
        $data = [];

        $data[static::KEY_ELEMENT] = $postConfigurationMetaCustom->getElement();
        $data[static::KEY_ATTRIBUTES] = $postConfigurationMetaCustom->getAttributes();

        return $data;
    }

    /**
     * @param array|PostConfigurationMetaCustom[] $array
     * @return array|array[]
     */
    public function toDataArray(array $array): array
    {
        $dataArray = [];

        foreach ($array as $postConfigurationMetaCustom) {
            $dataArray[] = $this->toData($postConfigurationMetaCustom);
        }

        return $dataArray;
    }
}
