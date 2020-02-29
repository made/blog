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

namespace Made\Blog\Engine\Repository\Implementation\Aggregation;

use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;

/**
 * Class PostConfigurationRepository
 * @package Made\Blog\Engine\Repository\Implementation\Aggregation
 */
class PostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    /**
     * @var array
     */
    private $implementations;

    /**
     * PostConfigurationRepository constructor.
     * @param array $implementations
     */
    public function __construct(array $implementations)
    {
        $this->implementations = $implementations;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlug(string $name): ?PostConfiguration
    {
        // TODO: Implement getOneBySlug() method.
    }

    /**
     * @inheritDoc
     */
    public function getOneBySlugRedirect(string $name): ?PostConfiguration
    {
        // TODO: Implement getOneBySlugRedirect() method.
    }

    /**
     * @inheritDoc
     */
    public function getAllByCategory(string ...$category): array
    {
        // TODO: Implement getAllByCategory() method.
    }

    /**
     * @inheritDoc
     */
    public function getAllByTag(string ...$tag): array
    {
        // TODO: Implement getAllByTag() method.
    }
}
