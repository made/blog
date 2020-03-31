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
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyPostConfigurationRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyPostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    use CacheProxyIdentityHelperTrait;

    const CACHE_KEY_ALL /*-----------*/ = 'pc-all';
    const CACHE_KEY_ONE_BY_ID /*-----*/ = 'pc-one-by-id';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CacheProxyPostConfigurationRepository constructor.
     * @param CacheInterface $cache
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, PostConfigurationRepositoryInterface $postConfigurationRepository, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->postConfigurationRepository = $postConfigurationRepository;
        $this->logger = $logger;
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
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];

        try {
            /** @var array|PostConfiguration[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->postConfigurationRepository
                ->getAll($criteria);

            if (!empty($all)) {
                try {
                    $this->cache->set($key, $all);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'criteria' => $criteria,
                        'key' => $key,
                        'exception' => $exception,
                    ]);
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
        $key = static::CACHE_KEY_ONE_BY_ID . '-' . $id;

        $one = null;

        try {
            /** @var null|PostConfiguration $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'id' => $id,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->postConfigurationRepository
                ->getOneById($id);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'id' => $id,
                        'key' => $key,
                        'exception' => $exception,
                    ]);
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

            $callbackMap = $filter->getCallbackMap();
            $filterName = $filterName . '_' . implode('_', array_keys($callbackMap));
        }

        $orderName = 'null';
        if (null !== ($order = $criteria->getOrder())) {
            $orderName = $order->getName();
        }

        $identity = $this->getIdentity([
            'class' => get_class(),
            'offset' /*--*/ => $offset,
            'limit' /*---*/ => $limit,
            'filter' /*--*/ => $filterName,
            'order' /*---*/ => $orderName,
        ], 'sha256');

        return "{$format}_{$identity}";
    }
}
