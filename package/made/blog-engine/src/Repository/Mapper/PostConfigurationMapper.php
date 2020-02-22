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
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;

/**
 * Class PostConfigurationMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationMapper
{
    const KEY_ID = 'id';
    const KEY_LOCALE = 'locale';

    /**
     * @var PostConfigurationLocaleMapper
     */
    private $postConfigurationLocaleMapper;

    /**
     * PostConfigurationMapper constructor.
     * @param PostConfigurationLocaleMapper $postConfigurationLocaleMapper
     */
    public function __construct(PostConfigurationLocaleMapper $postConfigurationLocaleMapper)
    {
        $this->postConfigurationLocaleMapper = $postConfigurationLocaleMapper;
    }

    /**
     * @param array $data
     * @return PostConfiguration
     * @throws MapperException
     */
    public function fromData(array $data): PostConfiguration
    {
        $postConfiguration = new PostConfiguration();

        // Required:
        if (isset($data[static::KEY_ID]) && is_string($data[static::KEY_ID])) {
            $postConfiguration->setId($data[static::KEY_ID]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_ID);
        }

        // Required:
        if (isset($data[static::KEY_LOCALE]) && is_array($data[static::KEY_LOCALE])) {
            $postConfiguration->setLocale(
                $this->postConfigurationLocaleMapper->fromDataArray($data[static::KEY_LOCALE])
            );
        } else {
            throw new MapperException('Missing key: ' . static::KEY_LOCALE);
        }

        return $postConfiguration;
    }

    /**
     * @param PostConfiguration $postConfiguration
     * @return array
     */
    public function toData(PostConfiguration $postConfiguration): array
    {
        $data = [];

        $data[static::KEY_ID] = $postConfiguration->getId();
        $data[static::KEY_LOCALE] = $this->postConfigurationLocaleMapper->toDataArray(
            $postConfiguration->getLocale()
        );

        return $data;
    }
}
