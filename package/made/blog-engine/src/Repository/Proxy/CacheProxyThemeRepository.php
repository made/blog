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

use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CachingThemeRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyThemeRepository implements ThemeRepositoryInterface
{
    const CACHE_KEY_ALL = 'theme-all';
    const CACHE_KEY_ONE = 'theme-one-%1$s';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;

    /**
     * @var ThemeMapper
     */
    private $themeMapper;

    /**
     * CacheProxyThemeRepository constructor.
     * @param CacheInterface $cache
     * @param ThemeRepositoryInterface $themeRepository
     * @param ThemeMapper $themeMapper
     */
    public function __construct(CacheInterface $cache, ThemeRepositoryInterface $themeRepository, ThemeMapper $themeMapper)
    {
        $this->cache = $cache;
        $this->themeRepository = $themeRepository;
        $this->themeMapper = $themeMapper;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function getAll(): array
    {
        // TODO: Cache expiry for gameplayjdk/php-file-cache!
        $key = static::CACHE_KEY_ALL;

        /** @var array|Theme[] $all */
        $allData = $this->cache->get($key, []);
        $all = [];

        try {
            $all = $this->themeMapper->fromDataArray($allData);
        } catch (MapperException $exception) {
            // TODO: Logging.
        }

        if (empty($all)) {
            $all = $this->themeRepository->getAll();

            if (!empty($all)) {
                $allData = $this->themeMapper->toDataArray($all);

                $this->cache->set($key, $allData);
            }
        }

        return $all;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function getOneByName(string $name): ?Theme
    {
        $key = vsprintf(static::CACHE_KEY_ONE, [
            $name,
        ]);

        $oneData = $this->cache->get($key);
        $one = null;

        try {
            $one = $this->themeMapper->fromData($oneData);
        } catch (MapperException $exception) {
            // TODO: Logging.
        }

        if (empty($one)) {
            $one = $this->themeRepository->getOneByName($name);

            if (!empty($one)) {
                $oneData = $this->themeMapper->toData($one);

                $this->cache->set($key, $oneData);
            }
        }

        return $one;
    }
}
