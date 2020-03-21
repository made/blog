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

use Made\Blog\Engine\Exception\InvalidArgumentException;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * Class PostService
 *
 * @package Made\Blog\Engine\Service
 */
class PostService
{
    /**
     * Path to the post folder relative to the root directory.
     */
    const PATH_POST = '/posts';

    /**
     * Path to the post configuration file (json).
     */
    const PATH_CONFIGURATION = 'configuration.json';

    /**
     * Path to the category configuration file (json).
     */
    const PATH_CONFIGURATION_CATEGORY = 'configuration_category.json';

    /**
     * Path to the tag configuration file (json).
     */
    const PATH_CONFIGURATION_TAG = 'configuration_tag.json';

    /**
     * Namespace for twig.
     */
    const NAMESPACE_POST = 'Post';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * PostService constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = $this->configuration->getRootDirectory();
        return Path::join(...[
            $path,
            static::PATH_POST,
        ]);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getNamespacePath(string $id): string
    {
        $namespace = static::NAMESPACE_POST;

        return "@{$namespace}/$id/content.md.twig";
    }

    /**
     * @param LoaderInterface $twigLoader
     * @throws InvalidArgumentException
     */
    public function updateLoader(LoaderInterface $twigLoader): void
    {
        if (!($twigLoader instanceof FilesystemLoader)) {
            throw new InvalidArgumentException('Unsupported ' . LoaderInterface::class . ' implementation: ' . get_class($twigLoader));
        }

        /** @var FilesystemLoader $twigLoader */

        $twigLoader->setPaths($this->getPath(), static::NAMESPACE_POST);
    }
}
