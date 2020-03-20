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

namespace Made\Blog\Engine\Repository\Proxy;


use Made\Blog\Engine\Model\Category;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyCategoryRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyCategoryRepository implements CategoryRepositoryInterface
{
    const CACHE_KEY_ALL = 'category-all';
    const CACHE_KEY_ONE = 'category-one-%1$s';
    const CACHE_KEY_ONE_BY_NAME = 'category-one-by-name-%1$s';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * CacheProxyCategoryRepository constructor.
     * @param CacheInterface $cache
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CacheInterface $cache, CategoryRepositoryInterface $categoryRepository)
    {
        $this->cache = $cache;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $key = static::CACHE_KEY_ALL;

        $all = [];

        try {
            /** @var array|Category[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->categoryRepository
                ->getAll($criteria);

            if (!empty($all)) {
                try {
                    $this->cache->set($key, $all);
                } catch (InvalidArgumentException $exception) {
                    // TODO: Log.
                }
            }
        }

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?Category
    {
        $key = vsprintf(static::CACHE_KEY_ONE, [
            $id,
        ]);

        $one = null;

        try {
            /** @var null|Category $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->categoryRepository
                ->getOneById($id);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    // TODO: Log.
                }
            }
        }

        return $one;
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Category
    {
        $key = vsprintf(static::CACHE_KEY_ONE_BY_NAME, [
            $name,
        ]);

        $one = null;

        try {
            /** @var null|Category $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->categoryRepository
                ->getOneByName($name);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    // TODO: Log.
                }
            }
        }

        return $one;
    }
}
