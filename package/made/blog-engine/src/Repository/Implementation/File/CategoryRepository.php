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

use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Help\Directory;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Category;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\CategoryMapper;
use Made\Blog\Engine\Service\PostService;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryRepository constructor.
     * @param Configuration $configuration
     * @param CategoryMapper $categoryMapper
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, CategoryMapper $categoryMapper, LoggerInterface $logger)
    {
        $this->configuration = $configuration;
        $this->categoryMapper = $categoryMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $configurationPath = $this->getConfigurationPath();
        $list = [];

        if (is_file($configurationPath)) {
            // Even if the category configuration file does not exist, this will return an empty array.
            $list = $this->getContent($configurationPath);
        } else {
            // TODO: Logging.
        }

        /** @var array|Category[] $all */
        $all = array_map(function (array $data): ?Category {
            if (empty($data)) {
                return null;
            }

            try {
                return $this->categoryMapper->fromData($data);
            } catch (MapperException $exception) {
                // TODO: Logging.
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?Category $category): bool {
            return null !== $category;
        });

        return $this->applyCriteria($criteria, $all, Category::class);
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?Category
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Category $carry, Category $one) use ($id): ?Category {
            if (null === $carry && strtolower($id) === strtolower($one->getId())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Category
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Category $carry, Category $one) use ($name): ?Category {
            if (null === $carry && strtolower($name) === strtolower($one->getName())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @return string
     */
    private function getPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            PostService::PATH_POST,
        ]);
    }

    /**
     * @return string
     */
    private function getConfigurationPath(): string
    {
        $path = $this->getPath();

        return Path::join(...[
            $path,
            PostService::PATH_CONFIGURATION_CATEGORY,
        ]);
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
