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
 *
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
