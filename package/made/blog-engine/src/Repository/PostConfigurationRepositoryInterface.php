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

use DateTime;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;

interface PostConfigurationRepositoryInterface
{
    const TAG_POST_CONFIGURATION_REPOSITORY = 'repository.post_configuration';

    /**
     * @return array|PostConfiguration[]
     */
    public function getAll(): array;

    /**
     * @param string $id
     * @return PostConfiguration|null
     */
    public function getOneById(string $id): ?PostConfiguration;

    /**
     * @param DateTime $dateTime
     * @return array|PostConfiguration[]
     */
    public function getAllByPostDate(DateTime $dateTime): array;

    /**
     * @param string ...$status
     * @return array|PostConfiguration[]
     */
    public function getAllByStatus(string ...$status): array;
}