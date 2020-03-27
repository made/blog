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

use DateTime;
use Help\Slug;
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Repository\Criteria\CriteriaLocale;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyPostRepository
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyPostRepository implements PostRepositoryInterface
{
    use CacheProxyIdentityHelperTrait;

    const CACHE_KEY_ALL /*-------------------*/ = 'p-all';
    const CACHE_KEY_ALL_BY_POST_DATE /*------*/ = 'p-all-by-post-date';
    const CACHE_KEY_ALL_BY_STATUS /*---------*/ = 'p-all-by-status';
    const CACHE_KEY_ALL_BY_CATEGORY /*-------*/ = 'p-all-by-category';
    const CACHE_KEY_ALL_BY_TAG /*------------*/ = 'p-all-by-tag';
    const CACHE_KEY_ONE_BY_ID /*-------------*/ = 'p-one-by-id';
    const CACHE_KEY_ONE_BY_SLUG /*-----------*/ = 'p-one-by-slug';
    const CACHE_KEY_ONE_BY_SLUG_REDIRECT /*--*/ = 'p-one-by-slug-redirect';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CacheProxyPostRepository constructor.
     * @param CacheInterface $cache
     * @param PostRepositoryInterface $postRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, PostRepositoryInterface $postRepository, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->postRepository = $postRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create(Post $post): bool
    {
        return $this->postRepository
            ->create($post);
    }

    /**
     * @inheritDoc
     */
    public function getAll(CriteriaLocale $criteria): array
    {
        $key = static::CACHE_KEY_ALL;
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->postRepository
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
    public function getAllByPostDate(CriteriaLocale $criteria, DateTime $dateTime): array
    {
        $key = static::CACHE_KEY_ALL_BY_POST_DATE . '-' . $dateTime
                ->format(PostConfigurationLocaleMapper::DTS_FORMAT);
        $key = $this->getCacheKeyForCriteria($key, $criteria);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'criteria' => $criteria,
                'dateTime' => $dateTime,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($all)) {
            $all = $this->postRepository
                ->getAllByPostDate($criteria, $dateTime);

            if (!empty($all)) {
                try {
                    $this->cache->set($key, $all);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'criteria' => $criteria,
                        'dateTime' => $dateTime,
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
    public function getAllByStatus(CriteriaLocale $criteria, string ...$statusList): array
    {
        natsort($statusList);

        $key = static::CACHE_KEY_ALL_BY_STATUS . '-%1$s';
        $key = $this->getCacheKeyForCriteria($key, $criteria);
        $keyList = array_map(function (string $status) use ($key): string {
            return vsprintf($key, [
                $status,
            ]);
        }, $statusList);
        unset($key);

        /** @var array|Post[] $allList */
        $allList = [];

        foreach ($statusList as $index => $status) {
            $key = $keyList[$index];

            /** @var array|Post[] $all */
            $all = [];

            try {
                /** @var array|Post[] $all */
                $all = $this->cache->get($key, []);
            } catch (InvalidArgumentException $exception) {
                $this->logger->error('Unable to get requested value from the cache.', [
                    'criteria' => $criteria,
                    'index' => $index,
                    'status' => $status,
                    'key' => $key,
                    'exception' => $exception,
                ]);
            }

            if (empty($all)) {
                $all = $this->postRepository
                    ->getAllByStatus($criteria, ...$statusList);

                if (!empty($all)) {
                    try {
                        $this->cache->set($key, $all);
                    } catch (InvalidArgumentException $exception) {
                        $this->logger->error('Unable to set requested value to the cache.', [
                            'criteria' => $criteria,
                            'index' => $index,
                            'status' => $status,
                            'key' => $key,
                            'exception' => $exception,
                        ]);
                    }
                }
            }

            array_push($allList, ...$all);
        }

        return $allList;
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(CriteriaLocale $criteria, string ...$categoryList): array
    {
        natsort($categoryList);

        $key = static::CACHE_KEY_ALL_BY_CATEGORY . '-%1$s';
        $key = $this->getCacheKeyForCriteria($key, $criteria);
        $keyList = array_map(function (string $status) use ($key): string {
            return vsprintf($key, [
                $status,
            ]);
        }, $categoryList);
        unset($key);

        /** @var array|Post[] $allList */
        $allList = [];

        foreach ($categoryList as $index => $category) {
            $key = $keyList[$index];

            /** @var array|Post[] $all */
            $all = [];

            try {
                /** @var array|Post[] $all */
                $all = $this->cache->get($key, []);
            } catch (InvalidArgumentException $exception) {
                $this->logger->error('Unable to get requested value from the cache.', [
                    'criteria' => $criteria,
                    'index' => $index,
                    'category' => $category,
                    'key' => $key,
                    'exception' => $exception,
                ]);
            }

            if (empty($all)) {
                $all = $this->postRepository
                    ->getAllByCategory($criteria, ...$categoryList);

                if (!empty($all)) {
                    try {
                        $this->cache->set($key, $all);
                    } catch (InvalidArgumentException $exception) {
                        $this->logger->error('Unable to set requested value to the cache.', [
                            'criteria' => $criteria,
                            'index' => $index,
                            'category' => $category,
                            'key' => $key,
                            'exception' => $exception,
                        ]);
                    }
                }
            }

            array_push($allList, ...$all);
        }

        return $allList;
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(CriteriaLocale $criteria, string ...$tagList): array
    {
        natsort($tagList);

        $key = static::CACHE_KEY_ALL_BY_TAG . '-%1$s';
        $key = $this->getCacheKeyForCriteria($key, $criteria);
        $keyList = array_map(function (string $status) use ($key): string {
            return vsprintf($key, [
                $status,
            ]);
        }, $tagList);
        unset($key);

        /** @var array|Post[] $allList */
        $allList = [];

        foreach ($tagList as $index => $tag) {
            $key = $keyList[$index];

            /** @var array|Post[] $all */
            $all = [];

            try {
                /** @var array|Post[] $all */
                $all = $this->cache->get($key, []);
            } catch (InvalidArgumentException $exception) {
                $this->logger->error('Unable to get requested value from the cache.', [
                    'criteria' => $criteria,
                    'index' => $index,
                    'tag' => $tag,
                    'key' => $key,
                    'exception' => $exception,
                ]);
            }

            if (empty($all)) {
                $all = $this->postRepository
                    ->getAllByTag($criteria, ...$tagList);

                if (!empty($all)) {
                    try {
                        $this->cache->set($key, $all);
                    } catch (InvalidArgumentException $exception) {
                        $this->logger->error('Unable to set requested value to the cache.', [
                            'criteria' => $criteria,
                            'index' => $index,
                            'tag' => $tag,
                            'key' => $key,
                            'exception' => $exception,
                        ]);
                    }
                }
            }

            array_push($allList, ...$all);
        }

        return $allList;
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $locale, string $id): ?Post
    {
        $key = static::CACHE_KEY_ONE_BY_ID . '-' . $id;
        $key = $this->getCacheKeyForLocale($key, $locale);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'locale' => $locale,
                'id' => $id,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->postRepository
                ->getOneById($locale, $id);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'locale' => $locale,
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
    public function getOneBySlug(string $locale, string $slug): ?Post
    {
        $slug = Slug::sanitize($slug);

        $key = static::CACHE_KEY_ONE_BY_SLUG . '-' . $slug;
        $key = $this->getCacheKeyForLocale($key, $locale);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'locale' => $locale,
                'slug' => $slug,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->postRepository
                ->getOneBySlug($locale, $slug);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'locale' => $locale,
                        'slug' => $slug,
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
    public function getOneBySlugRedirect(string $locale, string $slugRedirect): ?Post
    {
        $slugRedirect = Slug::sanitize($slugRedirect);

        $key = static::CACHE_KEY_ONE_BY_SLUG_REDIRECT . '-' . $slugRedirect;
        $key = $this->getCacheKeyForLocale($key, $locale);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('Unable to get requested value from the cache.', [
                'locale' => $locale,
                'slugRedirect' => $slugRedirect,
                'key' => $key,
                'exception' => $exception,
            ]);
        }

        if (empty($one)) {
            $one = $this->postRepository
                ->getOneBySlugRedirect($locale, $slugRedirect);

            if (!empty($one)) {
                try {
                    $this->cache->set($key, $one);
                } catch (InvalidArgumentException $exception) {
                    $this->logger->error('Unable to set requested value to the cache.', [
                        'locale' => $locale,
                        'slugRedirect' => $slugRedirect,
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
    public function modify(Post $post): bool
    {
        return $this->postRepository
            ->modify($post);
    }

    /**
     * @inheritDoc
     */
    public function destroy(Post $post): bool
    {
        return $this->postRepository
            ->destroy($post);
    }

    /**
     * @param string $format
     * @param CriteriaLocale $criteria
     * @return string
     */
    private function getCacheKeyForCriteria(string $format, CriteriaLocale $criteria): string
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

            $callbackMap =$filter->getCallbackMap();
            $filterName = $filterName . '_' . implode('_', array_keys($callbackMap));
        }

        $orderName = 'null';
        if (null !== ($order = $criteria->getOrder())) {
            $orderName = $order->getName();
        }

        $locale = $criteria->getLocale();

        $identity = $this->getIdentity([
            'class' => get_class(),
            'offset' /*--*/ => $offset,
            'limit' /*---*/ => $limit,
            'filter' /*--*/ => $filterName,
            'order' /*---*/ => $orderName,
            'locale' /*--*/ => $locale,
        ], 'sha256');

        return "{$format}_{$identity}";
    }

    /**
     * @param string $format
     * @param string $locale
     * @return string
     */
    private function getCacheKeyForLocale(string $format, string $locale): string
    {
        $identity = $this->getIdentity([
            'class' => get_class(),
            'locale' /*--*/ => $locale,
        ], 'sha256');

        return "{$format}_{$identity}";
    }
}
