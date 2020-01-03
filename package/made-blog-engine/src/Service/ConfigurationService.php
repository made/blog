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

namespace Made\Blog\Engine\Service;

use Made\Blog\Engine\Exception\ConfigurationException;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Package\PackageAbstract;

/**
 * Class ConfigurationService
 *
 * @package Made\Blog\Engine\Service
 */
class ConfigurationService
{
    // This class has potential to use the strategy pattern for loading of configuration. Keep that in mind.

    /**
     * Path to the configuration file relative to the root directory.
     */
    const PATH_CONFIGURATION = '/app/configuration.json';

    /**
     * @var string
     */
    private $path;

    /**
     * ConfigurationService constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param bool $shouldThrow
     * @return array
     * @throws ConfigurationException
     */
    public function getConfiguration(bool $shouldThrow = false): array
    {
        $content = $this->getContent();
        if (empty($content) && $shouldThrow) {
            // TODO: Add proper exception message.
            throw new ConfigurationException();
        }

        $content[Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY] = $this->path;

        $configuration = [];
        $configuration[PackageAbstract::SERVICE_NAME_CONFIGURATION] = $content;

        return $configuration;
    }

    /**
     * @return array
     */
    private function getContent(): array
    {
        $path = $this->getConfigurationPath();

        $content = File::read($path);
        return Json::decode($content);
    }

    /**
     * @return string
     */
    private function getConfigurationPath(): string
    {
        return Path::join(...[
            $this->path,
            static::PATH_CONFIGURATION,
        ]);
    }

    /** generated methods */

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
