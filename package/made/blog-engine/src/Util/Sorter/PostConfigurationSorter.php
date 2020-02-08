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

namespace Made\Blog\Engine\Util\Sorter;

use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;

class PostConfigurationSorter
{
    public const ORDER_DESC = 'DESC';
    public const ORDER_ASC = 'ASC';

    /**
     * @param array $posts
     * @param string $order
     * @return array|PostConfiguration[]
     */
    public static function sortByPostDate(array $posts, string $order = self::ORDER_DESC): array
    {
        usort($posts, function ($a, $b) use ($order) {
            /** @var PostConfiguration $a */
            /** @var PostConfiguration $b */
            if ($a->getPostDate() === $b->getPostDate()) return 0;

            if ($order === 'DESC') {
                return $a->getPostDate() > $b->getPostDate() ? -1 : 1;
            } else if ($order === 'ASC') {
                return $a->getPostDate() < $b->getPostDate() ? -1 : 1;
            } else {
                throw new PostConfigurationException("$order is not valid.");
            }
        });

        return $posts;
    }
}
