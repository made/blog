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

namespace Made\Blog\Engine\Repository\Implementation\File;

use Made\Blog\Engine\Exception\FailedOperationException;
use Help\Directory;
use Help\File;
use Help\Json;
use Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\ThemeService;

/**
 * Class ThemeRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class ThemeRepository implements ThemeRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ThemeMapper
     */
    private $themeMapper;

    /**
     * ThemeRepository constructor.
     * @param Configuration $configuration
     * @param ThemeMapper $themeMapper
     */
    public function __construct(Configuration $configuration, ThemeMapper $themeMapper)
    {
        $this->configuration = $configuration;
        $this->themeMapper = $themeMapper;
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $path = $this->getPath();

        $list = Directory::listCallback($path, function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            $themePath = $this->getThemePath($entry);
            $viewPath = $this->getViewPath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            // TODO: Logging if below check is failed.
            return is_dir($themePath) && is_dir($viewPath) && is_file($configurationPath);
        });

        $all = array_map(function (string $entry): ?Theme {
            $themePath = $this->getThemePath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            // TODO: Docs.
            //  By allowing manual definition, a theme can define another path to its own content root. This can be
            //  useful when delivering themes via composer packages. The post-install command can then copy the theme
            //  configuration to the theme folder. The theme view files are resolved while keeping autoloaded php stuff
            //  intact.
            if (!array_key_exists(ThemeMapper::KEY_PATH, $data)) {
                $data[ThemeMapper::KEY_PATH] = $themePath;
            }

            try {
                return $this->themeMapper->fromData($data);
            } catch (FailedOperationException $exception) {
                // TODO: Logging.
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?Theme $theme): bool {
            return null !== $theme;
        });

        return $this->applyCriteria($criteria, $all, Theme::class);
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Theme
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Theme $carry, Theme $theme) use ($name): ?Theme {
            if (null === $carry && $theme->getName() === $name) {
                $carry = $theme;
            }

            return $carry;
        }, null);
    }

    /**
     * @param string $path
     * @return array
     */
    private function getContent(string $path): array
    {
        $content = File::read($path);
        return Json::decode($content);
    }

    /**
     * @return string
     */
    private function getPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            ThemeService::PATH_THEME,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getThemePath(string $entry): string
    {
        $path = $this->getPath();

        return Path::join(...[
            $path,
            $entry,
        ]);
    }

    /**
     * TODO: Use ThemeService::getViewPath() instead.
     * @param string $entry
     * @return string
     */
    private function getViewPath(string $entry): string
    {
        $path = $this->getThemePath($entry);

        return Path::join(...[
            $path,
            ThemeService::PATH_VIEW,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getConfigurationPath(string $entry): string
    {
        $path = $this->getThemePath($entry);

        return Path::join(...[
            $path,
            ThemeService::PATH_CONFIGURATION,
        ]);
    }
}
