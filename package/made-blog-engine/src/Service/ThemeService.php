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

use Made\Blog\Engine\Exception\ThemeException;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * Class ThemeLoadingService
 * @package Made\Blog\Engine\Service
 */
class ThemeService
{
    /**
     * Path to the base view folder relative to the root directory.
     */
    const PATH_VIEW = '/view';

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
    public function getViewPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            static::PATH_VIEW,
        ]);
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
            throw new ThemeException();
        }

        /** @var FilesystemLoader $twigLoader */

        $themeList = $this->themeRepository->getAll();
        foreach ($themeList as $theme) {
            // The name is used as the namespace.
            $twigLoader->addPath($theme->getPath(), $theme->getName());
        }
    }
}
