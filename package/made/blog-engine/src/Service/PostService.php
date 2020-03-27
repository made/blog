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
     * Namespace for twig.
     */
    const NAMESPACE = 'Post';

    /**
     * @var PathService
     */
    private $pathService;

    /**
     * PostService constructor.
     * @param PathService $pathService
     */
    public function __construct(PathService $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getNamespacePath(string $id): string
    {
        $namespace = static::NAMESPACE;

        return "@{$namespace}/{$id}/content.md.twig";
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

        $path = $this->pathService
            ->getPathPost();

        $twigLoader->setPaths($path, static::NAMESPACE);
    }
}
