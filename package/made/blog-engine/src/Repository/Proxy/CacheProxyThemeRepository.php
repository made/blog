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

use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Criteria\Criteria;
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
    public function getAll(Criteria $criteria): array
    {
        $key = static::CACHE_KEY_ALL;

        $all = [];

        try {
            /** @var array|Theme[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->themeRepository
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
    public function getOneByName(string $name): ?Theme
    {
        $key = vsprintf(static::CACHE_KEY_ONE, [
            $name,
        ]);

        $one = null;

        try {
            /** @var null|Theme $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->themeRepository->getOneByName($name);

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
