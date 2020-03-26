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

namespace Made\Blog\Engine\Utility\ClosureInspection;

use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionType;

/**
 * Class ClosureInspection
 *
 * @package Made\Blog\Engine\Utility\ClosureInspection
 */
final class ClosureInspection
{
    /**
     * @var ReflectionFunction
     */
    private $reflectionFunction;

    /**
     * ClosureInspection constructor.
     * @param ReflectionFunction $reflectionFunction
     */
    private function __construct(ReflectionFunction $reflectionFunction)
    {
        $this->reflectionFunction = $reflectionFunction;
    }

    /**
     * @param Closure $closure
     * @return ClosureInspection
     * @throws ReflectionException
     */
    public static function on(Closure $closure): ClosureInspection
    {
        $reflectionFunction = new ReflectionFunction($closure);

        return new self($reflectionFunction);
    }

    /**
     * @param int $index
     * @param string $className
     * @return bool
     */
    public function isParameterTypeClass(int $index, string $className): bool
    {
        return null !== ($parameterClassName = $this->getParameterClass($index))
            && ($parameterClassName === $className);
    }

    /**
     * @param int $index
     * @return bool
     */
    public function isParameterTypeBuiltin(int $index): bool
    {
        $parameterType = $this->getParameterType($index);

        return null !== $parameterType && $parameterType->isBuiltin();
    }

    /**
     * @param int $index
     * @return bool
     */
    public function isParameterNullable(int $index): bool
    {
        $parameterType = $this->getParameterType($index);

        return null !== $parameterType && $parameterType->allowsNull();
    }

    /**
     * @param int $index
     * @return bool
     */
    public function isParameterTypeArray(int $index): bool
    {
        $parameter = $this->getParameter($index);

        return null !== $parameter && $parameter->isArray();
    }

    /**
     * @param int $index
     * @return bool
     */
    public function isParameterTypeVariadic(int $index): bool
    {
        $parameter = $this->getParameter($index);

        return null !== $parameter && $parameter->isVariadic();
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function getParameterClass(int $index): ?string
    {
        $parameter = $this->getParameter($index);

        return null !== $parameter ? $parameter->getClass() : null;
    }

    /**
     * @param int $index
     * @return ReflectionType|null
     */
    public function getParameterType(int $index): ?ReflectionType
    {
        $parameter = $this->getParameter($index);

        return null !== $parameter ? $parameter->getType() : null;
    }

    /**
     * @param int $index
     * @return ReflectionParameter|null
     */
    public function getParameter(int $index): ?ReflectionParameter
    {
        $parameterList = $this->getParameterList();

        return $parameterList[$index] ?? null;
    }

    /**
     * @return array|ReflectionParameter[]
     */
    public function getParameterList(): array
    {
        return $this->reflectionFunction
            ->getParameters();
    }

    /**
     * @return ReflectionFunction
     */
    public function getFunction(): ReflectionFunction
    {
        return $this->reflectionFunction;
    }
}
