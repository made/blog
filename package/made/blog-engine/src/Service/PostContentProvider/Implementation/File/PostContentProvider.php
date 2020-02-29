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

namespace Made\Blog\Engine\Service\PostContentProvider\Implementation\File;

use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\PostContent;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Service\PostContentProviderInterface;

/**
 * Class PostContentProvider
 *
 * TODO: Use task chain here.
 *  - Read File
 *  - Process Twig
 *  - Process Parsedown
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File
 */
class PostContentProvider implements PostContentProviderInterface
{
    /**
     * @inheritDoc
     */
    public function accept(string $origin): bool
    {
        return PostConfigurationRepositoryFile::class === $origin;
    }

    /**
     * @inheritDoc
     */
    public function provide(PostConfigurationLocale $postConfigurationLocale): PostContent
    {
        // TODO: Implement provide() method.
    }
}
