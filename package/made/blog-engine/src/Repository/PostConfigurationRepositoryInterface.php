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

namespace Made\Blog\Engine\Repository;

use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;

interface PostConfigurationRepositoryInterface
{
    const TAG_POST_REPOSITORY = 'repository.post';

    /**
     * @return array|PostConfiguration[]
     */
    public function getAll(): array;

    // ToDo: Remove all from here
    /**
     * Get any post configuration from slug name.
     * @param string $name
     * @return PostConfiguration|null
     */
    public function getOneBySlug(string $name): ?PostConfiguration;

    /**
     * Get any redirect post configuration from slug name.
     * @param string $name
     * @return PostConfiguration|null
     */
    public function getOneBySlugRedirect(string $name): ?PostConfiguration;

    /**
     * Get any redirect post configuration from one or more categories.
     * @param string ...$category
     * @return array|PostConfiguration[]
     */
    public function getAllByCategory(string ...$category): array;

    /**
     * Get any redirect post configuration from one or more tags.
     * @param string ...$tag
     * @return array|PostConfiguration[]
     */
    public function getAllByTag(string ...$tag): array;
}
