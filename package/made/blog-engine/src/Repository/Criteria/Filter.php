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
     * @var Closure
     */
    private $callback;

    /**
     * Filter constructor.
     * @param string $name
     * @param Closure $callback
     */
    public function __construct(string $name, Closure $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
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
     * @return Closure
     */
    public function getCallback(): Closure
    {
        return $this->callback;
    }

    /**
     * @param Closure $callback
     * @return Filter
     */
    public function setCallback(Closure $callback): Filter
    {
        $this->callback = $callback;
        return $this;
    }
}
