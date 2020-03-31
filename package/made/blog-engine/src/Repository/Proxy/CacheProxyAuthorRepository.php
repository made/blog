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

use Made\Blog\Engine\Model\Author;
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyAuthorRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyAuthorRepository implements AuthorRepositoryInterface
{
    use CacheProxyIdentityHelperTrait;

    const CACHE_KEY_ALL /*-------------------*/ = 'a-all';
    const CACHE_KEY_ALL_BY_LOCATION /*-------*/ = 'a-all-by-location';
    const CACHE_KEY_ONE_BY_ID /*-------------*/ = 'a-one-by-id';
    const CACHE_KEY_ONE_BY_NAME /*-----------*/ = 'a-one-by-name';
    const CACHE_KEY_ONE_BY_NAME_DISPLAY /*---*/ = 'a-one-by-name-display';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CacheProxyAuthorRepository constructor.
     * @param CacheInterface $cache
     * @param AuthorRepositoryInterface $authorRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, AuthorRepositoryInterface $authorRepository, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->authorRepository = $authorRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(Author $author): bool
    {
        return $this->authorRepository
            ->create($author);
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $key = $this->getCacheKeyForCriteria(static::CACHE_KEY_ALL, $criteria);

        $all = [];

        try {
            /** @var array|Author[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->authorRepository
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
    public function getAllByLocation(Criteria $criteria, string $location): array
    {
        $key = static::CACHE_KEY_ALL_BY_LOCATION . '-' . $location;
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];

        try {
            /** @var array|Author[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'location' => $location,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->authorRepository
                ->getAll($criteria);

            if (!empty($all)) {
                try {
                    $this->cache->set($key, $all);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'criteria' => $criteria,
                        'location' => $location,
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
    public function getOneByName(string $name): ?Author
    {
        $key = static::CACHE_KEY_ONE_BY_NAME . '-' . $name;

        try {
            /** @var null|Author $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'name' => $name,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->authorRepository
                ->getOneByName($name);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'name' => $name,
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
    public function getOneByNameDisplay(string $nameDisplay): ?Author
    {
        $key = static::CACHE_KEY_ONE_BY_NAME_DISPLAY . '-' . $nameDisplay;

        try {
            /** @var null|Author $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'nameDisplay' => $nameDisplay,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->authorRepository
                ->getOneByNameDisplay($nameDisplay);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'nameDisplay' => $nameDisplay,
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
    public function modify(Author $author): bool
    {
        return $this->authorRepository
            ->modify($author);
    }

    /**
     * @inheritDoc
     */
    public function destroy(Author $author): bool
    {
        return $this->authorRepository
            ->destroy($author);
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
