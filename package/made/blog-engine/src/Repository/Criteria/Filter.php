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

namespace Made\Blog\Engine\Repository\Criteria;

use Closure;

/**
 * Class Filter
 *
 * @package Made\Blog\Engine\Repository\Criteria
 */
class Filter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|Closure[]
     */
    private $callbackMap;

    /**
     * Filter constructor.
     * @param string $name
     * @param array|Closure[] $callbackMap
     */
    public function __construct(string $name, array $callbackMap)
    {
        $this->name = $name;
        $this->callbackMap = $callbackMap;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Filter
     */
    public function setName(string $name): Filter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array|Closure[]
     */
    public function getCallbackMap(): array
    {
        return $this->callbackMap;
    }

    /**
     * @param array|Closure[] $callbackMap
     * @return Filter
     */
    public function setCallbackMap(array $callbackMap)
    {
        $this->callbackMap = $callbackMap;
        return $this;
    }

    /**
     * @param string $key
     * @return Closure
     */
    public function getCallback(string $key): ?Closure
    {
        return $this->callbackMap[$key] ?? null;
    }

    /**
     * @param string $key
     * @param Closure $callback
     * @return Filter
     */
    public function setCallback(string $key, Closure $callback): Filter
    {
        $this->callbackMap[$key] = $callback;
        return $this;
    }
}
