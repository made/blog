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
use Made\Blog\Engine\Model\Post;

/**
 * Class PostMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostMapper
{
    const KEY_CONFIGURATION = 'configuration';
    const KEY_CONTENT = 'content';

    /**
     * @var PostConfigurationMapper
     */
    private $postConfigurationMapper;

    /**
     * @var PostContentMapper
     */
    private $postContentMapper;

    /**
     * PostMapper constructor.
     * @param PostConfigurationMapper $postConfigurationMapper
     * @param PostContentMapper $postContentMapper
     */
    public function __construct(PostConfigurationMapper $postConfigurationMapper, PostContentMapper $postContentMapper)
    {
        $this->postConfigurationMapper = $postConfigurationMapper;
        $this->postContentMapper = $postContentMapper;
    }

    /**
     * @param array $data
     * @return Post
     * @throws FailedOperationException
     */
    public function fromData(array $data): Post
    {
        $post = new Post();

        // Required:
        if (isset($data[static::KEY_CONFIGURATION]) && is_array($data[static::KEY_CONFIGURATION])) {
            $post->setConfiguration(
                $this->postConfigurationMapper->fromData($data[static::KEY_CONFIGURATION])
            );
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_CONFIGURATION);
        }

        // Required:
        if (isset($data[static::KEY_CONTENT]) && is_array($data[static::KEY_CONTENT])) {
            $post->setContent(
                $this->postContentMapper->fromData($data[static::KEY_CONTENT])
            );
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_CONTENT);
        }

        return $post;
    }

    /**
     * @param Post $post
     * @return array
     */
    public function toData(Post $post): array
    {
        $data = [];

        $data[static::KEY_CONFIGURATION] = $this->postConfigurationMapper
            ->toData($post->getConfiguration());
        $data[static::KEY_CONTENT] = $this->postContentMapper
            ->toData($post->getContent());

        return $data;
    }
}
