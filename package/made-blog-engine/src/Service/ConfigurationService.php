<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Made
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

use App\Package;
use Made\Blog\Engine\Exception\FileNotFoundException;
use Made\Blog\Engine\Exception\InvalidFormatException;

/**
 * Class ConfigurationService
 * @package Made\Blog\Engine\Service
 */
class ConfigurationService
{
    private const FILE_NAME_CONFIGURATION = '/app/configuration.json';

    /**
     * @param string $rootDirectory
     * @return array
     * @throws FileNotFoundException
     * @throws InvalidFormatException
     */
    public static function loadConfiguration(string $rootDirectory): array
    {
        if (!file_exists($rootDirectory . static::FILE_NAME_CONFIGURATION)) {
            throw new FileNotFoundException('Unfortunately the configuration.json file is not found in ' . $rootDirectory . '/app/');
        }

        $configFile = json_decode(file_get_contents($rootDirectory . static::FILE_NAME_CONFIGURATION), true);

        if (!$configFile) {
            throw new InvalidFormatException('Unfortunately the configuration.json is empty or has invalid values.');
        }

        $configuration = [];
        $configuration[Package::SERVICE_NAME_CONFIGURATION] = $configFile;
        $configuration[Package::SERVICE_NAME_CONFIGURATION]['root_directory'] = $rootDirectory;

        return $configuration;
    }
}
