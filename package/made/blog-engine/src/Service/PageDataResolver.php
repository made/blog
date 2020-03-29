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

use Help\Slug;
use Made\Blog\Engine\Exception\FailedOperationException;

/**
 * Class PageDataResolver
 *
 * @package Made\Blog\Engine\Service
 */
class PageDataResolver implements PageDataResolverInterface
{
    /**
     * @var array|PageDataProviderInterface[]
     */
    private $pageDataProviderList;

    /**
     * @var SlugParserInterface
     */
    private $slugParser;

    /**
     * PageDataResolver constructor.
     * @param array|PageDataProviderInterface[] $pageDataProviderList
     * @param SlugParserInterface $slugParser
     */
    public function __construct(array $pageDataProviderList, SlugParserInterface $slugParser)
    {
        $this->pageDataProviderList = $pageDataProviderList;
        $this->slugParser = $slugParser;
    }

    /**
     * @inheritDoc
     * @throws FailedOperationException
     */
    public function resolve(string $slug): ?array
    {
        $slug = Slug::sanitize($slug);

        $slugData = $this->slugParser
            ->parse($slug);

        if (null !== ($pageDataProvider = $this->getPageDataProvider($slugData))) {
            return $pageDataProvider->provide($slugData);
        }

        throw new FailedOperationException('Unable to resolve page data from slug: ' . $slug);
    }

    /**
     * @param array $slugData
     * @return PageDataProviderInterface
     */
    private function getPageDataProvider(array $slugData): PageDataProviderInterface
    {
        return array_reduce($this->pageDataProviderList, function (?PageDataProviderInterface $carry, PageDataProviderInterface $pageDataProvider) use ($slugData): ?PageDataProviderInterface {
            if (null === $carry && $pageDataProvider->accept($slugData)) {
                return $pageDataProvider;
            }

            return $carry;
        }, null);
    }
}
