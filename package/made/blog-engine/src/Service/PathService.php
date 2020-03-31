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
use Made\Blog\Engine\Model\Configuration;

/**
 * Class PathService
 *
 * @package Made\Blog\Engine\Service
 */
class PathService
{
    /**
     * Path to the theme folder relative to the root directory.
     */
    const PATH_THEME = '/theme';

    /**
     * Path to the theme base view folder relative to the root directory.
     */
    const PATH_THEME_VIEW = '/view';

    /**
     * Path to the theme configuration file (json).
     */
    const PATH_THEME_CONFIGURATION = '/theme.json';

    /**
     * Path to the post folder relative to the root directory.
     */
    const PATH_POST = '/posts';

    /**
     * Path to the post configuration file (json).
     */
    const PATH_POST_CONFIGURATION = 'configuration.json';

    /**
     * Path to the category configuration file (json).
     */
    const PATH_CATEGORY_CONFIGURATION = 'configuration_category.json';

    /**
     * Path to the tag configuration file (json).
     */
    const PATH_TAG_CONFIGURATION = 'configuration_tag.json';

    /**
     * Path to the author configuration file (json).
     */
    const PATH_AUTHOR_CONFIGURATION = 'configuration_author.json';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * PathService constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getPathTheme(): string
    {
        $path = $this->configuration->getRootDirectory();

        return Path::join(...[
            $path,
            static::PATH_THEME,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getPathThemeEntry(string $entry): string
    {
        $path = $this->getPathTheme();

        return Path::join(...[
            $path,
            $entry,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getPathThemeView(string $entry): string
    {
        $path = $this->getPathThemeEntry($entry);

        return Path::join(...[
            $path,
            static::PATH_THEME_VIEW,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getPathThemeConfiguration(string $entry): string
    {
        $path = $this->getPathThemeEntry($entry);

        return Path::join(...[
            $path,
            static::PATH_THEME_CONFIGURATION,
        ]);
    }

    /**
     * @return string
     */
    public function getPathPost(): string
    {
        $path = $this->configuration->getRootDirectory();

        return Path::join(...[
            $path,
            static::PATH_POST,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getPathPostEntry(string $entry): string
    {
        $path = $this->getPathPost();

        return Path::join(...[
            $path,
            $entry,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    public function getPathPostConfiguration(string $entry): string
    {
        $path = $this->getPathPostEntry($entry);

        return Path::join(...[
            $path,
            static::PATH_POST_CONFIGURATION,
        ]);
    }

    /**
     * @return string
     */
    public function getPathCategoryConfiguration(): string
    {
        $path = $this->getPathPost();

        return Path::join(...[
            $path,
            static::PATH_CATEGORY_CONFIGURATION,
        ]);
    }

    /**
     * @return string
     */
    public function getPathTagConfiguration(): string
    {
        $path = $this->getPathPost();

        return Path::join(...[
            $path,
            static::PATH_TAG_CONFIGURATION,
        ]);
    }

    /**
     * @return string
     */
    public function getPathAuthorConfiguration(): string
    {
        $path = $this->getPathPost();

        return Path::join(...[
            $path,
            static::PATH_AUTHOR_CONFIGURATION,
        ]);
    }
}
