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

namespace Made\Blog\Engine\Model;

/**
 * Class Configuration
 *
 * @package Made\Blog\Engine\Model
 */
class Configuration
{
    const CONFIGURATION_NAME_THEME = 'theme';
    const CONFIGURATION_NAME_ROOT_DIRECTORY = 'root_directory';

    /**
     * 'root_directory'
     *
     * @var string
     */
    private $rootDirectory;

    /**
     * 'theme'
     *
     * @var string
     */
    private $theme;

    /**
     * @return bool
     */
    public function hasTheme(): bool
    {
        return !empty($this->theme);
    }

    /** generated methods */

    /**
     * @return string
     */
    public function getRootDirectory(): string
    {
        return $this->rootDirectory;
    }

    /**
     * @param string $rootDirectory
     * @return Configuration
     */
    public function setRootDirectory(string $rootDirectory): Configuration
    {
        $this->rootDirectory = $rootDirectory;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return Configuration
     */
    public function setTheme(string $theme): Configuration
    {
        $this->theme = $theme;
        return $this;
    }
}
