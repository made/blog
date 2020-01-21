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

namespace Made\Blog\Engine\Repository\Implementation\Aggregation;

use Made\Blog\Engine\Model\Content\Content;
use Made\Blog\Engine\Repository\ContentRepositoryInterface;

/**
 * Class ContentRepository
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var array
     */
    private $implementations;

    /**
     * ContentRepository constructor.
     * @param array $implementations
     */
    public function __construct(array $implementations)
    {
        $this->implementations = $implementations;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $name): ?Content
    {
        // TODO: Implement getOneBySlug() method.
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $name): ?Content
    {
        // TODO: Implement getOneBySlugRedirect() method.
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$category): array
    {
        // TODO: Implement getAllByCategory() method.
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tag): array
    {
        // TODO: Implement getAllByTag() method.
    }
}
