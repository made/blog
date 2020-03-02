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
use Made\Blog\Engine\Model\Post;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CacheProxyPostRepository
 *
 * TODO: Check if a single-key cache would be better. That would not use natsort() and implode() on the parameter, but
 *  pull each parameter entry from the cache and make the result unique by id. E.g.: Find "tag-1, tag-2" would not
 *  result in the cache key "tag-1-tag-2", but in "tag-1" and "tag-2" separately.
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
class CacheProxyPostRepository implements PostRepositoryInterface
{
    const CACHE_KEY_ALL = 'post-all';
    const CACHE_KEY_ALL_BY_POST_DATE = 'post-all-by-post-date-%1$s';
    const CACHE_KEY_ALL_BY_STATUS = 'post-all-by-status-%1$s';
    const CACHE_KEY_ALL_BY_CATEGORY = 'post-all-by-category-%1$s';
    const CACHE_KEY_ALL_BY_TAG = 'post-all-by-tag-%1$s';
    const CACHE_KEY_ONE = 'post-one-%1$s';
    const CACHE_KEY_ONE_BY_SLUG = 'post-one-by-slug-%1$s';
    const CACHE_KEY_ONE_BY_SLUG_REDIRECT = 'post-one-by-slug-redirect-%1$s';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * CacheProxyPostRepository constructor.
     * @param CacheInterface $cache
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(CacheInterface $cache, PostRepositoryInterface $postRepository)
    {
        $this->cache = $cache;
        $this->postRepository = $postRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $key = static::CACHE_KEY_ALL;

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postRepository->getAll();

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
    public function getAllByPostDate(DateTime $dateTime): array
    {
        $key = vsprintf(static::CACHE_KEY_ALL_BY_POST_DATE, [
            $dateTime->format(PostConfigurationLocaleMapper::DTS_FORMAT),
        ]);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postRepository->getAllByPostDate($dateTime);

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
    public function getAllByStatus(string ...$statusList): array
    {
        natsort($statusList);

        $key = vsprintf(static::CACHE_KEY_ALL_BY_STATUS, [
            implode('-', $statusList),
        ]);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postRepository->getAllByStatus(...$statusList);

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
    public function getAllByCategory(string ...$categoryList): array
    {
        natsort($categoryList);

        $key = vsprintf(static::CACHE_KEY_ALL_BY_CATEGORY, [
            implode('-', $categoryList),
        ]);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postRepository->getAllByCategory(...$categoryList);

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
    public function getAllByTag(string ...$tagList): array
    {
        natsort($tagList);

        $key = vsprintf(static::CACHE_KEY_ALL_BY_TAG, [
            implode('-', $tagList),
        ]);

        $all = [];

        try {
            /** @var array|Post[] $all */
            $all = $this->cache->get($key, []);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($all)) {
            $all = $this->postRepository->getAllByTag(...$tagList);

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
    public function getOneById(string $id): ?Post
    {
        $key = vsprintf(static::CACHE_KEY_ONE, [
            $id,
        ]);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->postRepository->getOneById($id);

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
    public function getOneBySlug(string $slug): ?Post
    {
        $key = vsprintf(static::CACHE_KEY_ONE_BY_SLUG, [
            $slug,
        ]);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->postRepository->getOneBySlug($slug);

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
    public function getOneBySlugRedirect(string $slugRedirect): ?Post
    {
        $key = vsprintf(static::CACHE_KEY_ONE_BY_SLUG_REDIRECT, [
            $slugRedirect,
        ]);

        $one = null;

        try {
            /** @var null|Post $one */
            $one = $this->cache->get($key, null);
        } catch (InvalidArgumentException $exception) {
            // TODO: Log.
        }

        if (empty($one)) {
            $one = $this->postRepository->getOneBySlugRedirect($slugRedirect);

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
