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

namespace Made\Blog\Engine\Repository\Implementation\File;

use Help\Directory;
use Help\File;
use Help\Json;
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Model\Theme;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\ThemeMapper;
use Made\Blog\Engine\Repository\ThemeRepositoryInterface;
use Made\Blog\Engine\Service\PathService;
use Psr\Log\LoggerInterface;

/**
 * Class ThemeRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class ThemeRepository implements ThemeRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var PathService
     */
    private $pathService;

    /**
     * @var ThemeMapper
     */
    private $themeMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ThemeRepository constructor.
     * @param PathService $pathService
     * @param ThemeMapper $themeMapper
     * @param LoggerInterface $logger
     */
    public function __construct(PathService $pathService, ThemeMapper $themeMapper, LoggerInterface $logger)
    {
        $this->pathService = $pathService;
        $this->themeMapper = $themeMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $path = $this->pathService
            ->getPathTheme();

        $list = Directory::listCallback($path, function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            $themePath = $this->pathService
                ->getPathThemeEntry($entry);
            $viewPath = $this->pathService
                ->getPathThemeView($entry);
            $configurationPath = $this->pathService
                ->getPathThemeConfiguration($entry);

            $valid = is_dir($themePath) && is_dir($viewPath) && is_file($configurationPath);

            if (!$valid) {
                $this->logger->warning('Invalid directory in theme path!', [
                    'entry' => $entry,
                    'themePath' => $themePath,
                    'viewPath' => $viewPath,
                    'configurationPath' => $configurationPath,
                    'valid' => $valid,
                ]);
            }

            return $valid;
        });

        $all = array_map(function (string $entry): ?Theme {
            $themePath = $this->pathService
                ->getPathThemeEntry($entry);
            $configurationPath = $this->pathService
                ->getPathThemeConfiguration($entry);

            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            // TODO: Docs.
            //  By allowing manual definition, a theme can define another path to its own content root. This can be
            //  useful when delivering themes via composer packages. The post-install command can then copy the theme
            //  configuration to the theme folder. The theme view files are resolved while keeping autoloaded php stuff
            //  intact.
            if (!array_key_exists(ThemeMapper::KEY_PATH, $data)) {
                $data[ThemeMapper::KEY_PATH] = $themePath;
            }

            try {
                return $this->themeMapper
                    ->fromData($data);
            } catch (FailedOperationException $exception) {
                $this->logger->error('Unable to map category data to a valid object. This is likely caused by some malformed format.', [
                    'data' => $data,
                    'exception' => $exception,
                ]);
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?Theme $theme): bool {
            return null !== $theme;
        });

        return $this->applyCriteria($criteria, $all, Theme::class);
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Theme
    {
        $all = $this->getAll(new Criteria());

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
}
