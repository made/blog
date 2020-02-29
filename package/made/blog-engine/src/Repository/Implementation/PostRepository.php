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

namespace Made\Blog\Engine\Repository\Implementation;

use Made\Blog\Engine\Repository\PostConfigurationLocaleRepositoryInterface;
use Made\Blog\Engine\Repository\PostRepositoryInterface;
use Made\Blog\Engine\Service\PostContentProviderInterface;
use Made\Blog\Engine\Service\PostContentResolver;

/**
 * Class PostRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var PostConfigurationLocaleRepositoryInterface
     */
    private $postConfigurationLocaleRepository;

    /**
     * @var PostContentResolver
     */
    private $postContentResolver;

    /**
     * PostRepository constructor.
     * @param PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository
     * @param PostContentResolver $postContentResolver
     */
    public function __construct(PostConfigurationLocaleRepositoryInterface $postConfigurationLocaleRepository, PostContentResolver $postContentResolver)
    {
        $this->postConfigurationLocaleRepository = $postConfigurationLocaleRepository;
        $this->postContentResolver = $postContentResolver;
    }
}
