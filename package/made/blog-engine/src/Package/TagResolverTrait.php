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

namespace Made\Blog\Engine\Package;

use ArrayObject;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;

/**
 * Trait TagResolverTrait
 *
 * TODO: Use pimple-package-utility v1.1 when available.
 *
 * @package Made\Blog\Engine\Package
 */
trait TagResolverTrait
{
    /**
     * @param string $tagName
     * @param string|null $parentClassName
     * @param array|null $excludeClassName
     * @return array
     * @throws PackageException
     */
    protected function resolveTag(string $tagName, ?string $parentClassName = null, ?array $excludeClassName = null): array
    {
        if (!$this instanceof PackageAbstract || !isset($this->container) || !method_exists($this, 'hasTagSupport')) {
            throw new PackageException('Container not found.');
        }

        if (!$this->hasTagSupport(true)) {
            return [];
        }

        /** @var ArrayObject $tag */
        $tag = $this->container[PackageAbstract::SERVICE_NAME_TAG];

        if (isset($tag[$tagName]) && is_array($tag[$tagName])) {
            $tagArray = $tag[$tagName];

            return array_reduce($tagArray, function (array $carry, string $serviceName) use ($parentClassName, $excludeClassName): array {
                if (null !== $excludeClassName && in_array($serviceName, $excludeClassName)) {
                    return $carry;
                }

                $service = $this->container[$serviceName] ?? null;

                if (null === $parentClassName || (null !== $parentClassName && is_subclass_of($service, $parentClassName, true))) {
                    $carry[$serviceName] = $service;
                }

                return $carry;
            }, []);
        }

        throw new PackageException('Unable to resolve tag.');
    }
}
