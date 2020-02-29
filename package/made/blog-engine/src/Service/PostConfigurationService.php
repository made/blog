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

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;

class PostConfigurationService
{
    /**
     * ToDo: Make this configurable.
     * @var string
     */
    const PATH_POSTS = '/posts';

    /**
     * ToDo: Make this configurable later.
     * Name of the configuration file which is needed for each blog post
     *
     * @var string
     */
    const PATH_CONFIGURATION = 'configuration.json';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * PostConfigurationService constructor.
     * @param Configuration $configuration
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     */
    public function __construct(Configuration $configuration, PostConfigurationRepositoryInterface $postConfigurationRepository)
    {
        $this->configuration = $configuration;
        $this->postConfigurationRepository = $postConfigurationRepository;
    }
}
