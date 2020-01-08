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

namespace Made\Blog\Engine\Repository\Proxy;

use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class CachingThemeRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyThemeRepository implements ThemeRepositoryInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;

    /**
     * CacheProxyThemeRepository constructor.
     * @param CacheInterface $cache
     * @param ThemeRepositoryInterface $themeRepository
     */
    public function __construct(CacheInterface $cache, ThemeRepositoryInterface $themeRepository)
    {
        $this->cache = $cache;
        $this->themeRepository = $themeRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        // TODO: Implement getAll() method.
        return $this->themeRepository->getAll();
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Theme
    {
        // TODO: Implement getOneByName() method.
        return $this->themeRepository->getOneByName($name);
    }
}
