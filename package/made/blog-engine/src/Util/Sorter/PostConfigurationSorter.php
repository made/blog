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

/**
 * Class PostConfigurationSorter
 *
 * @package Made\Blog\Engine\Util\Sorter
 */
class PostConfigurationSorter
{
    public const ORDER_DESC = 'DESC';
    public const ORDER_ASC = 'ASC';

    /**
     * @param array|PostConfiguration[] $all
     * @param string|null $order
     * @return array|PostConfiguration[]
     */
    public static function sortByPostDate(array $all, ?string $order = null): array
    {
        if (null === $order) {
            $order = static::ORDER_DESC;
        }

        usort($all, function (PostConfiguration $a, PostConfiguration $b) use ($order) {
            if ($a->getDate() === $b->getDate()) {
                return 0;
            }

            if ($order === static::ORDER_DESC) {
                return $a->getDate() > $b->getDate() ? -1 : 1;
            } else if ($order === static::ORDER_ASC) {
                return $a->getDate() < $b->getDate() ? -1 : 1;
            } else {
                throw new PostConfigurationException("Order '$order' is not valid.");
            }
        });

        return $all;
    }
}
