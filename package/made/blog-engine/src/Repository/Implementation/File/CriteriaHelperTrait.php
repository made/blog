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

namespace Made\Blog\Engine\Repository\Implementation\File;

use Closure;
use InvalidArgumentException;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Utility\ClosureInspection\ClosureInspection;
use ReflectionException;

/**
 * Trait CriteriaHelperTrait
 *
 * TODO: Make this a service.
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
trait CriteriaHelperTrait
{
    /**
     * @param Criteria $criteria
     * @param array $all
     * @param string $className
     * @return array
     */
    private function applyCriteria(Criteria $criteria, array $all, string $className)
    {
        if (!empty($all)) {
            $all = $this->applyCriteriaFilter($criteria, $all, $className);
            $all = $this->orderCriteriaOrder($criteria, $all, $className);
            $all = $this->applyCriteriaSlice($criteria, $all, $className);
        }

        return $all;
    }

    /**
     * @param Criteria $criteria
     * @param array $all
     * @param string $className
     * @return array
     */
    private function applyCriteriaFilter(Criteria $criteria, array $all, string $className): array
    {
        if ($criteria->getScope() === $className && null !== ($filter = $criteria->getFilter())) {
            $callback = $filter->getCallback();

            if (null !== $callback) {
                $callbackInspection = $this->createInspection($callback);

                if (null !== $callbackInspection && $callbackInspection->isParameterTypeClass(0, $className)) {
                    $all = array_filter($all, $callback);
                }
            }
        }

        return $all;
    }

    /**
     * @param Criteria $criteria
     * @param array $all
     * @param string $className
     * @return array
     */
    private function orderCriteriaOrder(Criteria $criteria, array $all, string $className): array
    {
        if ($criteria->getScope() === $className && null !== ($order = $criteria->getOrder())) {
            $comparator = $order->getComparator();
            $comparatorInspection = $this->createInspection($comparator);

            if (null !== $comparatorInspection
                && $comparatorInspection->isParameterTypeClass(0, $className)
                && $comparatorInspection->isParameterTypeClass(1, $className)) {
                usort($all, $comparator);
            }
        }

        return $all;
    }

    /**
     * TODO: Further testing is needed.
     *
     * @param Criteria $criteria
     * @param array $all
     * @param string $className
     * @return array
     */
    private function applyCriteriaSlice(Criteria $criteria, array $all, string $className): array
    {
        if ($criteria->getScope() === $className) {
            $offset = $criteria->getOffset();
            if (-1 === $offset) {
                $offset = null;
            }

            $limit = $criteria->getLimit();
            if (-1 === $limit) {
                $limit = null;
            }

            /** @var array $slice Fix for broken type hint. */
            $all = $slice = array_slice($all, $offset, $limit);
        }

        return $all;
    }

    /**
     * @param Closure $callback
     * @return ClosureInspection|null
     */
    private function createInspection(Closure $callback): ?ClosureInspection
    {
        try {
            return ClosureInspection::on($callback);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException('Could not inspect closure!', 0, $exception);
        }
    }
}
