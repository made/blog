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

/**
 * Class Criteria
 *
 * @package Made\Blog\Engine\Repository\Criteria
 */
class Criteria
{
    /**
     * @var string|null
     */
    private $scope;

    /**
     * @var int
     */
    private $offset = -1;

    /**
     * @var int
     */
    private $limit = -1;

    /**
     * @var Order|null
     */
    private $order;

    /**
     * @var Filter|null
     */
    private $filter;

    /**
     * Criteria constructor.
     * @param string|null $scope
     */
    public function __construct(?string $scope = null)
    {
        $this->scope = $scope;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return Criteria
     */
    public function setScope(?string $scope): Criteria
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return Criteria
     */
    public function setOffset(int $offset): Criteria
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return Criteria
     */
    public function setLimit(int $limit): Criteria
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order|null $order
     * @return Criteria
     */
    public function setOrder(?Order $order): Criteria
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Filter|null
     */
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    /**
     * @param Filter|null $filter
     * @return Criteria
     */
    public function setFilter(?Filter $filter): Criteria
    {
        $this->filter = $filter;
        return $this;
    }
}
