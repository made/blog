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

use Help\Path;
use Made\Blog\Engine\Exception\InvalidArgumentException;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * Class ThemeLoadingService
 *
 * @package Made\Blog\Engine\Service
 */
class ThemeService
{
    /**
     * Namespace for Twig.
     */
    const NAMESPACE = 'App';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PathService
     */
    private $pathService;

    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;

    /**
     * ThemeService constructor.
     * @param Configuration $configuration
     * @param PathService $pathService
     * @param ThemeRepositoryInterface $themeRepository
     */
    public function __construct(Configuration $configuration, PathService $pathService, ThemeRepositoryInterface $themeRepository)
    {
        $this->configuration = $configuration;
        $this->pathService = $pathService;
        $this->themeRepository = $themeRepository;
    }

    /**
     * @return array
     */
    public function getNamespaceAndPath(): array
    {
        $path = $this->configuration
            ->getRootDirectory();

        return [
            static::NAMESPACE => $this->getPathThemeView($path),
        ];
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

        // TODO: Add order.
        $themeListCriteria = new Criteria(Theme::class);
        $themeList = $this->themeRepository
            ->getAll($themeListCriteria);

        foreach ($themeList as $theme) {
            $name = $theme->getName();

            $path = $theme->getPath();
            $path = $this->getPathThemeView($path);

            // The name of the theme is used as the namespace.
            $twigLoader->setPaths($path, $name);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getPathThemeView(string $path): string
    {
        return Path::join(...[
            $path,
            PathService::PATH_THEME_VIEW,
        ]);
    }
}
