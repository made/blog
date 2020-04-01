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

namespace Cache\Psr16;

use Cache\Cache as PhpCache;
use Cache\Exception\CacheException;
use Cache\Psr16\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 *
 * @package Cache\Psr16
 */
final class Cache implements CacheInterface
{
    const SANITIZE_PATTERN = '/[^a-zA-Z0-9\-]/';
    const SANITIZE_REPLACEMENT = '-';

    /**
     * @var PhpCache
     */
    private $cache;

    /**
     * @var int
     */
    private $cacheExpiryTime;

    /**
     * Cache constructor.
     * @param PhpCache $cache
     */
    public function __construct(PhpCache $cache)
    {
        $this->cache = $cache;
        $this->cacheExpiryTime = strtotime('+1 Day', 0);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $key = $this->sanitize($key);
        $value = null;

        try {
            if (!$this->expired($key)) {
                $value = $this->cache->get($key);
            }
        } catch (CacheException $exception) {
            throw new InvalidArgumentException();
        }

        return $value ?: $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->sanitize($key);

        try {
            $this->cache->set($key, $value);

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $key = $this->sanitize($key);

        try {
            $this->cache->del($key);

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        try {
            $this->cache->clr();

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("Key is not traversable!");
        }

        $values = [];

        foreach ($keys as $key) {
            $key = $this->sanitize($key);

            $values[] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_iterable($values)) {
            throw new InvalidArgumentException("Value is not traversable!");
        }

        $success = true;

        foreach ($values as $key => $value) {
            $key = $this->sanitize($key);

            if (!$this->set($key, $values, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("Key is not traversable!");
        }

        $success = true;

        foreach ($keys as $key) {
            $key = $this->sanitize($key);

            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $key = $this->sanitize($key);
        $present = false;

        try {
            $present = $this->cache->has($key);
        } catch (CacheException $exception) {
        }

        return $present;
    }

    /**
     * Sanitize the given key. Replace anything matching the `SANITIZE_PATTERN` with the `SANITIZE_REPLACEMENT`.
     *
     * @param string|null $key
     * @return string|null
     * @throws InvalidArgumentException
     */
    private function sanitize($key): ?string
    {
        if (empty($key) || !is_string($key) || (null === ($clean = preg_replace(static::SANITIZE_PATTERN, static::SANITIZE_REPLACEMENT, $key)))) {
            throw new InvalidArgumentException("Key '$key' is not a legal value!");
        }

        return $clean;
    }

    /**
     * Check if the entry for the given key is expired.
     *
     * @param string|null $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function expired($key)
    {
        $key = $this->sanitize($key);
        $expired = false;

        try {
            $cacheExpiryTime = time() - $this->cacheExpiryTime;

            $expired = $this->cache->exp($key, $cacheExpiryTime);
        } catch (CacheException $exception) {
        }

        return $expired;
    }

    /**
     * @return int
     */
    public function getCacheExpiryTime(): int
    {
        return $this->cacheExpiryTime;
    }

    /**
     * @param int $cacheExpiryTime
     * @return Cache
     */
    public function setCacheExpiryTime(int $cacheExpiryTime): Cache
    {
        $this->cacheExpiryTime = $cacheExpiryTime;
        return $this;
    }
}
