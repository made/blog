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
use Made\Blog\Engine\Model\PostConfigurationMeta;

/**
 * Class PostConfigurationMetaMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationMetaMapper
{
    use NormalizeValueArrayTrait;

    const KEY_AUTHOR = 'author';
    const KEY_PUBLISHER = 'publisher';
    const KEY_DESCRIPTION = 'description';
    const KEY_KEYWORDS = 'keywords';
    const KEY_ROBOTS = 'robots';
    const KEY_CUSTOM_META_LIST = 'custom_meta';

    /**
     * @var PostConfigurationMetaCustomMapper
     */
    private $postConfigurationMetaCustomMapper;

    /**
     * PostConfigurationMetaMapper constructor.
     * @param PostConfigurationMetaCustomMapper $postConfigurationMetaCustomMapper
     */
    public function __construct(PostConfigurationMetaCustomMapper $postConfigurationMetaCustomMapper)
    {
        $this->postConfigurationMetaCustomMapper = $postConfigurationMetaCustomMapper;
    }

    /**
     * @param array $data
     * @return PostConfigurationMeta
     * @throws FailedOperationException
     */
    public function fromData(array $data): PostConfigurationMeta
    {
        $postConfigurationMeta = new PostConfigurationMeta();

        // Optional:
        if (isset($data[static::KEY_AUTHOR]) && is_string($data[static::KEY_AUTHOR])) {
            $postConfigurationMeta->setAuthor($data[static::KEY_AUTHOR]);
        }

        // Optional:
        if (isset($data[static::KEY_PUBLISHER]) && is_string($data[static::KEY_PUBLISHER])) {
            $postConfigurationMeta->setPublisher($data[static::KEY_PUBLISHER]);
        }

        // Optional:
        if (isset($data[static::KEY_DESCRIPTION]) && is_string($data[static::KEY_DESCRIPTION])) {
            $postConfigurationMeta->setDescription($data[static::KEY_DESCRIPTION]);
        }

        // Optional:
        if (isset($data[static::KEY_KEYWORDS]) && is_array($data[static::KEY_KEYWORDS]) && !empty($keywords = $this->normalizeValueArray($data[static::KEY_KEYWORDS], true))) {
            $postConfigurationMeta->setKeywords($keywords);
        }

        // Optional:
        if (isset($data[static::KEY_ROBOTS]) && is_string($data[static::KEY_ROBOTS])) {
            $postConfigurationMeta->setRobots($data[static::KEY_ROBOTS]);
        }

        // Optional:
        if (isset($data[static::KEY_CUSTOM_META_LIST]) && is_array($data[static::KEY_CUSTOM_META_LIST])) {
            $postConfigurationMeta->setCustomMetaList(
                $this->postConfigurationMetaCustomMapper->fromDataArray($data[static::KEY_CUSTOM_META_LIST])
            );
        }

        return $postConfigurationMeta;
    }

    /**
     * @param PostConfigurationMeta $postConfigurationMeta
     * @return array
     */
    public function toData(PostConfigurationMeta $postConfigurationMeta): array
    {
        $data = [];

        if (!$postConfigurationMeta->hasCustomMetaList()) {
            $postConfigurationMeta->setCustomMetaList([]);
        }

        $data[static::KEY_AUTHOR] = $postConfigurationMeta->getAuthor();
        $data[static::KEY_PUBLISHER] = $postConfigurationMeta->getPublisher();
        $data[static::KEY_DESCRIPTION] = $postConfigurationMeta->getDescription();
        $data[static::KEY_KEYWORDS] = $postConfigurationMeta->getKeywords();
        $data[static::KEY_ROBOTS] = $postConfigurationMeta->getRobots();
        $data[static::KEY_CUSTOM_META_LIST] = $this->postConfigurationMetaCustomMapper->toDataArray(
            $postConfigurationMeta->getCustomMetaList()
        );

        return $data;
    }
}
