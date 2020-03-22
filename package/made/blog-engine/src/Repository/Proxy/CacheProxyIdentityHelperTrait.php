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

namespace Made\Blog\Engine\Repository\Proxy;

/**
 * Trait CacheProxyIdentityHelperTrait
 *
 * @package Made\Blog\Engine\Repository\Proxy
 */
trait CacheProxyIdentityHelperTrait
{
    /**
     * @param array $identityMap
     * @param string|null $hashAlgorithm
     * @return string
     */
    private function getIdentity(array $identityMap, ?string $hashAlgorithm = null): string
    {
        $identity = implode('_', array_map(function ($key, string $value): string {
            if (is_string($key)) {
                $value = "{$key}-{$value}";
            }

            return $value;
        }, array_keys($identityMap), array_values($identityMap)));

        if (null !== $hashAlgorithm) {
            $identity = hash($hashAlgorithm, $identity, false);
        }

        return $identity;
    }
}
