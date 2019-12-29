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

use Made\Blog\Engine\Exception\ThemeNotFoundException;
use Made\Blog\Engine\Model\Configuration;
use Slim\Views\Twig;
use Twig\Error\LoaderError;

/**
 * Class ThemeLoadingService
 * @package Made\Blog\Engine\Service
 */
class ThemeLoadingService
{
    /**
     * ToDo: Put this in the config object which is stored in the container
     * Name of the directory in which the themes are stored.
     */
    protected const THEME_BASE_DIRECTORY = '/theme/';

    /**
     * ToDo: Put this in the config object which is stored in the container
     * Name of the directory in which the views are stored.
     */
    protected const THEME_VIEW_DIRECTORY = '/view/';

    /**
     * Name of the configuration node in the configuration.json
     */
    protected const THEME_CONFIGURATION_NAME = 'theme';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * ThemeLoadingService constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Twig
     * @throws LoaderError
     * @throws ThemeNotFoundException
     */
    public function loadTheme()
    {
        if (!$this->configuration->hasTheme()) {
            throw new ThemeNotFoundException('No configured theme has been found in the configuration.');
        }

        // ToDo: Path cleaner
        $path = $this->configuration->getRootDirectory() . '/' . static::THEME_BASE_DIRECTORY . '/' . $this->configuration->getTheme() . '/' . static::THEME_VIEW_DIRECTORY;

        if (!is_dir($path)) {
            throw new ThemeNotFoundException('Unfortunately there is no theme called ' . $this->configuration->getTheme() . '. Please check your configuration.');
        }

        return Twig::create($path, [
            'cache' => false, // ToDo: Caching should be configurable - due to development it is deactived for now
        ]);
    }
}
