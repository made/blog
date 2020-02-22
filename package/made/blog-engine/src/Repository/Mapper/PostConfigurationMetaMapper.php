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
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationMeta;

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
    const KEY_CUSTOM_META = 'custom_meta';

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
     * @throws MapperException
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
        if (isset($data[static::KEY_CUSTOM_META]) && is_array($data[static::KEY_CUSTOM_META])) {
            $postConfigurationMeta->setCustomMeta(
                $this->postConfigurationMetaCustomMapper->fromDataArray($data[static::KEY_CUSTOM_META])
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

        $data[static::KEY_AUTHOR] = $postConfigurationMeta->getAuthor();
        $data[static::KEY_PUBLISHER] = $postConfigurationMeta->getPublisher();
        $data[static::KEY_DESCRIPTION] = $postConfigurationMeta->getDescription();
        $data[static::KEY_KEYWORDS] = $postConfigurationMeta->getKeywords();
        $data[static::KEY_ROBOTS] = $postConfigurationMeta->getRobots();
        $data[static::KEY_CUSTOM_META] = $this->postConfigurationMetaCustomMapper->toDataArray(
            $postConfigurationMeta->getCustomMeta()
        );

        return $data;
    }
}
