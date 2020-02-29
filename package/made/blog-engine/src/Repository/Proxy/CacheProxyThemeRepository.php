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
