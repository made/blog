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
use Made\Blog\Engine\Model\PostContent;

/**
 * Class PostContentMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostContentMapper
{
    const KEY_CONTENT = 'content';

    /**
     * @param array $data
     * @return PostContent
     * @throws FailedOperationException
     */
    public function fromData(array $data): PostContent
    {
        $postContent = new PostContent();

        // Required:
        if (isset($data[static::KEY_CONTENT]) && is_string($data[static::KEY_CONTENT])) {
            $postContent->setContent($data[static::KEY_CONTENT]);
        } else {
            throw new FailedOperationException('Missing key: ' . static::KEY_CONTENT);
        }

        return $postContent;
    }

    /**
     * @param PostContent $postContent
     * @return array
     */
    public function toData(PostContent $postContent): array
    {
        $data = [];

        $data[static::KEY_CONTENT] = $postContent->getContent();

        return $data;
    }
}
