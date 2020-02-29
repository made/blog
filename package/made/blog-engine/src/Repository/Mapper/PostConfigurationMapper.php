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
use Made\Blog\Engine\Model\PostConfiguration;

/**
 * Class PostConfigurationMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationMapper
{
    const KEY_ID = 'id';
    const KEY_LOCALE_LIST = 'locale';

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
        if (isset($data[static::KEY_LOCALE_LIST]) && is_array($data[static::KEY_LOCALE_LIST])) {
            $postConfiguration->setLocaleList(
                $this->postConfigurationLocaleMapper->fromDataArray($data[static::KEY_LOCALE_LIST])
            );
        } else {
            throw new MapperException('Missing key: ' . static::KEY_LOCALE_LIST);
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
        $data[static::KEY_LOCALE_LIST] = $this->postConfigurationLocaleMapper->toDataArray(
            $postConfiguration->getLocaleList()
        );

        return $data;
    }
}
