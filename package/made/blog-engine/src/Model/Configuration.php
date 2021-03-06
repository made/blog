<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 Made
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
