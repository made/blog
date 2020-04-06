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

namespace Made\Blog\Engine\Repository\Criteria\Factory;

use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\Author;
use Made\Blog\Engine\Model\Category;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\Criteria\Order;

/**
 * Class OrderFactory
 *
 * @package Made\Blog\Engine\Repository\Criteria\Factory
 */
final class OrderFactory
{
    const ORDER_DESC /*---*/ = 'DESC';
    const ORDER_ASC /*----*/ = 'ASC';

    /**
     * @return Order|null
     */
    public static function none(): ?Order
    {
        return null;
    }

    /**
     * Target: {@link PostConfigurationLocale}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byDate_PostConfigurationLocale(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (PostConfigurationLocale $a, PostConfigurationLocale $b): int {
                if ($a->getDate() === $b->getDate()) {
                    return 0;
                }

                return $a->getDate() > $b->getDate() ? -1 : 1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (PostConfigurationLocale $a, PostConfigurationLocale $b): int {
                if ($a->getDate() === $b->getDate()) {
                    return 0;
                }

                return $a->getDate() < $b->getDate() ? -1 : 1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Category}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byId_Category(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Category $a, Category $b): int {
                return strcmp($a->getId(), $b->getId()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Category $a, Category $b): int {
                return strcmp($a->getId(), $b->getId()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Category}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byName_Category(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Category $a, Category $b): int {
                return strcmp($a->getName(), $b->getName()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Category $a, Category $b): int {
                return strcmp($a->getName(), $b->getName()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Tag}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byId_Tag(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Tag $a, Tag $b): int {
                return strcmp($a->getId(), $b->getId()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Tag $a, Tag $b): int {
                return strcmp($a->getId(), $b->getId()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Tag}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byName_Tag(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Tag $a, Tag $b): int {
                return strcmp($a->getName(), $b->getName()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Tag $a, Tag $b): int {
                return strcmp($a->getName(), $b->getName()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Author}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byName_Author(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Author $a, Author $b): int {
                return strcmp($a->getName(), $b->getName()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Author $a, Author $b): int {
                return strcmp($a->getName(), $b->getName()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * Target: {@link Author}
     *
     * @param string|null $order
     * @return Order
     * @throws FailedOperationException
     */
    public static function byNameDisplay_Author(?string $order = null): Order
    {
        $order = static::normalizeOrder($order);
        $comparator = null;

        if (static::ORDER_DESC === $order) {
            $comparator = function (Author $a, Author $b): int {
                return strcmp($a->getNameDisplay(), $b->getNameDisplay()) * -1;
            };
        }

        if (static::ORDER_ASC === $order) {
            $comparator = function (Author $a, Author $b): int {
                return strcmp($a->getNameDisplay(), $b->getNameDisplay()) * +1;
            };
        }

        if (null === $comparator) {
            throw new FailedOperationException('Unable to match requested order: ' . $order);
        }

        return new Order(__METHOD__, $comparator);
    }

    /**
     * @param string|null $order
     * @return string
     */
    public static function normalizeOrder(?string $order): string
    {
        if (null === $order) {
            $order = static::ORDER_DESC;
        } else {
            $order = strtoupper($order);
            $order = trim($order);
        }

        return $order;
    }
}
