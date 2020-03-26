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

namespace Made\Blog\Engine\Service;

use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\PostContent;

/**
 * Class PostContentResolver
 *
 * @package Made\Blog\Engine\Service
 */
class PostContentResolver implements PostContentResolverInterface
{
    /**
     * @var array|PostContentProviderInterface[]
     */
    private $postContentProviderList;

    /**
     * PostContentResolver constructor.
     * @param array|PostContentProviderInterface[] $postContentProviderList
     */
    public function __construct(array $postContentProviderList)
    {
        $this->postContentProviderList = $postContentProviderList;
    }

    /**
     * @param PostConfigurationLocale $postConfigurationLocale
     * @return PostContent
     * @throws FailedOperationException
     */
    public function resolve(PostConfigurationLocale $postConfigurationLocale): ?PostContent
    {
        $origin = $postConfigurationLocale->getOrigin();

        if (null !== ($postContentProvider = $this->getPostContentProvider($origin))) {
            return $postContentProvider->provide($postConfigurationLocale);
        }

        throw new FailedOperationException('Unable to resolve post content from origin: ' . $origin);
    }

    /**
     * @param string $origin
     * @return PostContentProviderInterface|null
     */
    private function getPostContentProvider(string $origin): ?PostContentProviderInterface
    {
        return array_reduce($this->postContentProviderList, function (?PostContentProviderInterface $carry, PostContentProviderInterface $postContentProvider) use ($origin): ?PostContentProviderInterface {
            if (null === $carry && $postContentProvider->accept($origin)) {
                return $postContentProvider;
            }

            return $carry;
        }, null);
    }
}
