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

use Closure;
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\PostConfigurationLocale;

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
     * @return Closure|null
     */
    public function none(): ?Closure
    {
        return null;
    }

    /**
     * Target: PostConfigurationLocale
     *
     * @param string|null $order
     * @return Closure
     * @throws FailedOperationException
     */
    public function byDate(?string $order = null): Closure
    {
        if (null === $order) {
            $order = static::ORDER_DESC;
        } else {
            $order = strtoupper($order);
            $order = trim($order);
        }

        if (static::ORDER_DESC === $order) {
            return function (PostConfigurationLocale $a, PostConfigurationLocale $b) {
                if ($a->getDate() === $b->getDate()) {
                    return 0;
                }

                return $a->getDate() > $b->getDate() ? -1 : 1;
            };
        }

        if (static::ORDER_ASC === $order) {
            return function (PostConfigurationLocale $a, PostConfigurationLocale $b) {
                if ($a->getDate() === $b->getDate()) {
                    return 0;
                }

                return $a->getDate() < $b->getDate() ? -1 : 1;
            };
        }

        throw new FailedOperationException('Unable to match requested order: ' . $order);
    }
}
