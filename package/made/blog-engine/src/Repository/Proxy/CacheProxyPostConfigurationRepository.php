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

use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyPostConfigurationRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyPostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    const CACHE_KEY_ALL = 'post-configuration-all';
    const CACHE_KEY_ONE = 'post-configuration-one-%1$s';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * CacheProxyPostConfigurationRepository constructor.
     * @param CacheInterface $cache
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     */
    public function __construct(CacheInterface $cache, PostConfigurationRepositoryInterface $postConfigurationRepository)
    {
        $this->cache = $cache;
        $this->postConfigurationRepository = $postConfigurationRepository;
    }

    /**
     * @inheritDoc
     */
    public function modify(PostConfiguration $postConfiguration): bool
    {
        return $this->postConfigurationRepository
            ->modify($postConfiguration);
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $key = static::CACHE_KEY_ALL;

        $all = [];

        try {
            /** @var array|PostConfiguration[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postConfigurationRepository
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
    public function getOneById(string $id): ?PostConfiguration
    {
        $key = vsprintf(static::CACHE_KEY_ONE, [
            $id,
        ]);

        $one = null;

        try {
            /** @var null|PostConfiguration $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->postConfigurationRepository->getOneById($id);

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
    public function create(PostConfiguration $postConfiguration): bool
    {
        return $this->postConfigurationRepository
            ->create($postConfiguration);
    }

    /**
     * @inheritDoc
     */
    public function destroy(PostConfiguration $postConfiguration): bool
    {
        return $this->postConfigurationRepository
            ->destroy($postConfiguration);
    }
}
