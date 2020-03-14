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

use Made\Blog\Engine\Exception\ThemeException;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Twig\Error\LoaderError;
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
     * Path to the theme folder relative to the root directory.
     */
    const PATH_THEME = '/theme';

    /**
     * Path to the theme base view folder relative to the root directory.
     */
    const PATH_VIEW = '/view';

    /**
     * Path to the theme configuration file (json).
     */
    const PATH_CONFIGURATION = '/theme.json';

    /**
     * Namespace for Twig.
     */
    const NAMESPACE_VIEW = 'App';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ThemeRepositoryInterface
     */
    private $themeRepository;

    /**
     * ThemeService constructor.
     * @param Configuration $configuration
     * @param ThemeRepositoryInterface $themeRepository
     */
    public function __construct(Configuration $configuration, ThemeRepositoryInterface $themeRepository)
    {
        $this->configuration = $configuration;
        $this->themeRepository = $themeRepository;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = $this->configuration->getRootDirectory();
        return $this->getViewPath($path);
    }

    /**
     * @return array
     */
    public function getPathAndNamespace(): array
    {
        return [
            static::NAMESPACE_VIEW => $this->getPath(),
        ];
    }

    /**
     * @param LoaderInterface $twigLoader
     * @throws LoaderError
     * @throws ThemeException
     */
    public function updateLoader(LoaderInterface $twigLoader): void
    {
        if (!($twigLoader instanceof FilesystemLoader)) {
            // TODO: Add proper exception message.
            throw new ThemeException('Unsupported ' . LoaderInterface::class . ' implementation!');
        }

        /** @var FilesystemLoader $twigLoader */

        // TODO: Add order.
        $themeListCriteria = new Criteria();
        $themeList = $this->themeRepository->getAll($themeListCriteria);

        foreach ($themeList as $theme) {
            $path = $theme->getPath();
            // The name of the theme is used as the namespace.
            $twigLoader->addPath($this->getViewPath($path), $theme->getName());
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getViewPath(string $path): string
    {
        return Path::join(...[
            $path,
            static::PATH_VIEW,
        ]);
    }
}
