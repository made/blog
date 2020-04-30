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
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CachingThemeRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyThemeRepository implements ThemeRepositoryInterface
{
    use CacheProxyIdentityHelperTrait;

    const CACHE_KEY_ALL /*-----------*/ = 'th-all';
    const CACHE_KEY_ONE_BY_NAME /*---*/ = 'th-one-by-name';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CacheProxyThemeRepository constructor.
     * @param CacheInterface $cache
     * @param ThemeRepositoryInterface $themeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, ThemeRepositoryInterface $themeRepository, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->themeRepository = $themeRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $key = static::CACHE_KEY_ALL;
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];
        $fromCache = false;

        try {
            /** @var array|Theme[] $all */
            $all = $this->cache->get($key, []);

            $fromCache = true;
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->themeRepository
                ->getAll($criteria);

            $fromCache = false;
        }

        if (!$fromCache && !empty($all)) {
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

        return $all;
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Theme
    {
        $key = static::CACHE_KEY_ONE_BY_NAME . '-' . $name;

        $one = null;
        $fromCache = false;

        try {
            /** @var null|Theme $one */
            $one = $this->cache->get($key, null);

            $fromCache = true;
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'name' => $name,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->themeRepository
                ->getOneByName($name);

            $fromCache = false;
        }

        if (!$fromCache && !empty($one)) {
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

        return $one;
    }

    /**
     * @param string $format
     * @param Criteria $criteria
     * @return string
     */
    private function getCacheKeyForCriteria(string $format, Criteria $criteria): string
    {
        $scope = $criteria->getScope();
        if (null === $scope) {
            $scope = 'null';
        }

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
            'class' /*---*/ => get_class(),
            'scope' /*---*/ => $scope,
            'offset' /*--*/ => $offset,
            'limit' /*---*/ => $limit,
            'filter' /*--*/ => $filterName,
            'order' /*---*/ => $orderName,
        ], 'sha256');

        return "{$format}_{$identity}";
    }
}
