<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Made
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Made\Blog\Engine\Package;

use ArrayObject;
use Pimple\Package\Exception\PackageException;
use Pimple\Package\PackageAbstract;

/**
 * Trait TagResolverTrait
 *
 * @package Made\Blog\Engine\Package
 */
trait TagResolverTrait
{
    /**
     * Resolve to the first service with given tag.
     *
     * TODO: Resolve logic is insufficient with multiple implementations.
     *
     * @param string $tagName
     * @param string|null $parentClassName
     * @return mixed
     * @throws PackageException
     */
    protected function resolveTag(string $tagName, ?string $parentClassName = null)
    {
        if (!isset($this->container) || !method_exists($this, 'hasTagSupport')) {
            throw new PackageException('Container not found.');
        }

        $this->hasTagSupport(true);

        /** @var ArrayObject $tag */
        $tag = $this->container[PackageAbstract::SERVICE_NAME_TAG];

        if (isset($tag[$tagName]) && is_array($tag[$tagName])) {
            $tagArray = $tag[$tagName];

            return array_reduce($tagArray, function (array $carry, string $serviceName) use ($parentClassName): array {
                $service = $this->container[$serviceName] ?? null;

                if (null === $parentClassName || (null !== $parentClassName && is_subclass_of($service, $parentClassName))) {
                    $carry[$serviceName] = $service;
                }

                return $carry;
            }, []);
        }

        throw new PackageException('Unable to resolve tag.');
    }
}
