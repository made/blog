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
use Twig\Loader\FilesystemLoader;

/**
 * Class ThemeLoadingService
 * @package Made\Blog\Engine\Service
 */
class ThemeService
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
     * @var Twig
     */
    private $twig;

    /**
     * @var Configuration
     */
    protected $configuration;



    /**
     * ThemeLoadingService constructor.
     * @param Twig $twig
     * @param Configuration $configuration
     */
    public function __construct(Twig $twig, Configuration $configuration)
    {
        $this->twig = $twig;
        $this->configuration = $configuration;
    }

    // TODO: Load all themes.

    /**
     * @return ThemeService
     * @throws LoaderError
     * @throws ThemeNotFoundException
     */
    public function loadTheme(): ThemeService
    {
        if (!$this->configuration->hasTheme()) {
            throw new ThemeNotFoundException('No configured theme has been found in the configuration.');
        }

        // ToDo: Path cleaner.
        $path = $this->configuration->getRootDirectory() . '/' . static::THEME_BASE_DIRECTORY . '/' . $this->configuration->getTheme() . '/' . static::THEME_VIEW_DIRECTORY;

        if (!is_dir($path)) {
            throw new ThemeNotFoundException('Unfortunately there is no theme called ' . $this->configuration->getTheme() . '. Please check your configuration.');
        }

        // TODO: Negotiate template namespace name.
        $loader = $this->twig->getLoader();

        if (!($loader instanceof FilesystemLoader)) {
            throw new ThemeNotFoundException('Themes are only supported using the twig filesystem loader.');
        }

        /** @var FilesystemLoader $loader */
        $loader->addPath($path);

        return $this;
    }
}
