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
 * Class CriteriaBuilder
 *
 * @package Made\Blog\Engine\Repository\Criteria
 */
final class CriteriaBuilder
{
    /**
     * @return CriteriaBuilder
     */
    public static function create(): CriteriaBuilder
    {
        return new self();
    }

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
     * CriteriaBuilder constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return Criteria
     */
    public function build(): Criteria
    {
        return (new Criteria())
            ->setOffset($this->offset)
            ->setLimit($this->limit)
            ->setOrder($this->order)
            ->setFilter($this->filter);
    }

    /**
     * @param int $offset
     * @return CriteriaBuilder
     */
    public function withOffset(int $offset): CriteriaBuilder
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int $limit
     * @return CriteriaBuilder
     */
    public function withLimit(int $limit): CriteriaBuilder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return CriteriaBuilder
     */
    public function withOffsetAndLimit(int $offset, int $limit): CriteriaBuilder
    {
        return $this
            ->withOffset($offset)
            ->withLimit($limit);
    }

    /**
     * @param Order|null $order
     * @return CriteriaBuilder
     */
    public function withOrder(?Order $order): CriteriaBuilder
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param Filter|null $filter
     * @return CriteriaBuilder
     */
    public function withFilter(?Filter $filter): CriteriaBuilder
    {
        $this->filter = $filter;
        return $this;
    }
}
