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

use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyTagRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyTagRepository implements TagRepositoryInterface
{
    use CacheProxyIdentityHelperTrait;

    const CACHE_KEY_ALL /*-----------*/ = 't-all';
    const CACHE_KEY_ONE_BY_ID /*-----*/ = 't-one-by-id';
    const CACHE_KEY_ONE_BY_NAME /*---*/ = 't-one-by-name';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * CacheProxyTagRepository constructor.
     * @param CacheInterface $cache
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(CacheInterface $cache, TagRepositoryInterface $tagRepository)
    {
        $this->cache = $cache;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @inheritDoc
     */
    public function create(Tag $tag): bool
    {
        return $this->tagRepository
            ->create($tag);
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $key = static::CACHE_KEY_ALL;
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];

        try {
            /** @var array|Tag[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->tagRepository
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
    public function getOneById(string $id): ?Tag
    {
        $key = static::CACHE_KEY_ONE_BY_ID . '-' . $id;

        $one = null;

        try {
            /** @var null|Tag $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->tagRepository
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
    public function getOneByName(string $name): ?Tag
    {
        $key = static::CACHE_KEY_ONE_BY_NAME . '-' . $name;

        $one = null;

        try {
            /** @var null|Tag $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->tagRepository
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

    /**
     * @inheritDoc
     */
    public function modify(Tag $tag): bool
    {
        return $this->tagRepository
            ->modify($tag);
    }

    /**
     * @inheritDoc
     */
    public function destroy(Tag $tag): bool
    {
        return $this->tagRepository
            ->destroy($tag);
    }

    /**
     * @param string $format
     * @param Criteria $criteria
     * @return string
     */
    private function getCacheKeyForCriteria(string $format, Criteria $criteria): string
    {
        $offset = $criteria->getOffset();
        if (-1 === $offset) {
            $offset = 'null';
        }

        $limit = $criteria->getLimit();
        if (-1 === $limit) {
            $limit = 'null';
        }

        $filterName = 'null';
        if (null !== ($filter = $criteria->getFilter())) {
            $filterName = $filter->getName();
        }

        $orderName = 'null';
        if (null !== ($order = $criteria->getOrder())) {
            $orderName = $order->getName();
        }

        $identity = $this->getIdentity([
            'offset' /*--*/ => $offset,
            'limit' /*---*/ => $limit,
            'filter' /*--*/ => $filterName,
            'order' /*---*/ => $orderName,
        ], 'sha256');

        return "{$format}_{$identity}";
    }
}
