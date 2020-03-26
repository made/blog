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

use Made\Blog\Engine\Exception\InvalidArgumentException;

/**
 * Class CriteriaLocaleBuilder
 *
 * @package Made\Blog\Engine\Repository\Criteria
 */
final class CriteriaLocaleBuilder
{
    /**
     * @return CriteriaLocaleBuilder
     */
    public static function create(): CriteriaLocaleBuilder
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
     * @var string
     */
    private $locale;

    /**
     * CriteriaLocaleBuilder constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return CriteriaLocale
     * @throws InvalidArgumentException
     */
    public function build(): CriteriaLocale
    {
        if (null === $this->locale) {
            throw new InvalidArgumentException('Building ' . CriteriaLocale::class . ' without a locale set is not supported!');
        }

        return (new CriteriaLocale($this->locale))
            ->setOffset($this->offset)
            ->setLimit($this->limit)
            ->setOrder($this->order)
            ->setFilter($this->filter);
    }

    /**
     * @param int $offset
     * @return CriteriaLocaleBuilder
     */
    public function withOffset(int $offset): CriteriaLocaleBuilder
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int $limit
     * @return CriteriaLocaleBuilder
     */
    public function withLimit(int $limit): CriteriaLocaleBuilder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return CriteriaLocaleBuilder
     */
    public function withOffsetAndLimit(int $offset, int $limit): CriteriaLocaleBuilder
    {
        return $this
            ->withOffset($offset)
            ->withLimit($limit);
    }

    /**
     * @param Order|null $order
     * @return CriteriaLocaleBuilder
     */
    public function withOrder(?Order $order): CriteriaLocaleBuilder
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param Filter|null $filter
     * @return CriteriaLocaleBuilder
     */
    public function withFilter(?Filter $filter): CriteriaLocaleBuilder
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param string $locale
     * @return CriteriaLocaleBuilder
     */
    public function withLocale(string $locale): CriteriaLocaleBuilder
    {
        $this->locale = $locale;
        return $this;
    }
}
