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

use Help\File;
use Help\Json;
use Help\Path;
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
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
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var PostService
     */
    private $postService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryRepository constructor.
     * @param CategoryMapper $categoryMapper
     * @param PostService $postService
     * @param LoggerInterface $logger
     */
    public function __construct(CategoryMapper $categoryMapper, PostService $postService, LoggerInterface $logger)
    {
        $this->categoryMapper = $categoryMapper;
        $this->postService = $postService;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Category $category): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $configurationPath = $this->getConfigurationPath();
        $list = [];

        if (!is_readable($configurationPath)) {
            $this->logger->notice('Empty category file at configuration path.', [
                'configurationPath' => $configurationPath,
            ]);

            return $list;
        }

        // Even if the category configuration file would not exist, this will return an empty array.
        $list = $this->getContent($configurationPath);

        $list = array_filter($list, 'is_array');

        /** @var array|Category[] $all */
        $all = array_map(function (array $data): ?Category {
            if (empty($data)) {
                return null;
            }

            try {
                return $this->categoryMapper->fromData($data);
            } catch (FailedOperationException $exception) {
                $this->logger->error('Unable to map category data to a valid object. This is likely caused by some malformed format.', [
                    'data' => $data,
                    'exception' => $exception,
                ]);
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
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(Category $category): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Category $category): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @return string
     */
    private function getConfigurationPath(): string
    {
        $path = $this->postService->getPath();

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
