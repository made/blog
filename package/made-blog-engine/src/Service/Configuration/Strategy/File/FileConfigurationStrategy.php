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

namespace Made\Blog\Engine\Service\Configuration\Strategy\File;

use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Service\Configuration\Strategy\ConfigurationStrategyInterface;

/**
 * Class FileConfigurationStrategy
 *
 * @package Made\Blog\Engine\Service\Configuration\Strategy\File
 */
class FileConfigurationStrategy implements ConfigurationStrategyInterface
{
    /**
     * Path to the configuration file relative to the root directory.
     */
    const PATH_CONFIGURATION = '/app/configuration.json';

    /**
     * @var string|null
     */
    protected static $rootDirectoryPath;

    /**
     * @param string|null $rootDirectoryPath
     */
    public static function setRootDirectoryPath(?string $rootDirectoryPath): void
    {
        self::$rootDirectoryPath = $rootDirectoryPath;
    }

    /**
     * @return array
     */
    public function initialize(): array
    {
        // Set the path if it is not yet set.
        self::$rootDirectoryPath = self::$rootDirectoryPath ?: $this->findConfigurationPath();

        // Get the content.
        $content = $this->getConfigurationContent();
        // Add the root directory. This is required!
        $content[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY] = self::$rootDirectoryPath;

        return $content;
    }

    /**
     * @return array
     */
    protected function getConfigurationContent(): array
    {
        $path = $this->getConfigurationPath();

        $content = File::read($path);
        return Json::decode($content);
    }

    /**
     * @return string
     */
    protected function getConfigurationPath(): string
    {
        return Path::join(...[
            self::$rootDirectoryPath,
            static::PATH_CONFIGURATION,
        ]);
    }

    /**
     * TODO: Proper implementation. Go up until the PATH_CONFIGURATION exists relative to that directory.
     *
     * @return string
     */
    protected function findConfigurationPath(): string
    {
        // We need to go up 8 levels from vendor folder and 7 levels from (current) package folder.
        return dirname(__DIR__, 8);
    }
}
