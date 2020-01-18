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

namespace Made\Blog\Engine\Repository\Implementation\File;

use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Help\Directory;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Theme;
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
    public function getAll(): array
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

            $data[ThemeMapper::KEY_PATH] = $themePath;

            try {
                return $this->themeMapper->fromData($data);
            } catch (MapperException $exception) {
                // TODO: Logging.
            }

            return null;
        }, $list);

        return array_filter($all, function (?Theme $theme): bool {
            return null !== $theme;
        });
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Theme
    {
        $all = $this->getAll();

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
