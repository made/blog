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

use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Help\Directory;
use Help\File;
use Help\Json;
use Help\Path;
use Help\Slug;
use Made\Blog\Engine\Model\Category;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\CategoryRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\CategoryMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\Mapper\TagMapper;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Service\PostService;
use Psr\Log\LoggerInterface;

/**
 * Class PostConfigurationRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class PostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var array
     */
    private $defaultData;

    /**
     * @var PostConfigurationMapper
     */
    private $postConfigurationMapper;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var TagMapper
     */
    private $tagMapper;

    /**
     * @var PostService
     */
    private $postService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostConfigurationRepository constructor.
     * @param array $defaultData
     * @param PostConfigurationMapper $postConfigurationMapper
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryMapper $categoryMapper
     * @param TagRepositoryInterface $tagRepository
     * @param TagMapper $tagMapper
     * @param PostService $postService
     * @param LoggerInterface $logger
     */
    public function __construct(array $defaultData, PostConfigurationMapper $postConfigurationMapper, CategoryRepositoryInterface $categoryRepository, CategoryMapper $categoryMapper, TagRepositoryInterface $tagRepository, TagMapper $tagMapper, PostService $postService, LoggerInterface $logger)
    {
        $this->defaultData = $defaultData;
        $this->postConfigurationMapper = $postConfigurationMapper;
        $this->categoryRepository = $categoryRepository;
        $this->categoryMapper = $categoryMapper;
        $this->tagRepository = $tagRepository;
        $this->tagMapper = $tagMapper;
        $this->postService = $postService;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(PostConfiguration $postConfiguration): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $path = $this->postService->getPath();

        /** @var array|string[] $list */
        $list = Directory::listCallback($path, function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            $postPath = $this->getPostPath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            $valid = is_dir($postPath) && is_file($configurationPath);

            if (!$valid) {
                $this->logger->warning('Invalid directory in post path!', [
                    'entry' => $entry,
                    'postPath' => $postPath,
                    'configurationPath' => $configurationPath,
                    'valid' => $valid,
                ]);
            }

            return $valid;
        });

        /** @var array|PostConfiguration[] $all */
        $all = array_map(function (string $entry): ?PostConfiguration {
            $configurationPath = $this->getConfigurationPath($entry);
            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            $data[PostConfigurationMapper::KEY_ID] = $entry;
            $data = $this->provisionIntersectingData($data);

            try {
                return $this->postConfigurationMapper->fromData($data);
            } catch (FailedOperationException $exception) {
                $this->logger->error('Unable to map post configuration data to a valid object. This is likely caused by some malformed format.', [
                    'data' => $data,
                    'exception' => $exception,
                ]);
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?PostConfiguration $postConfiguration): bool {
            return null !== $postConfiguration;
        });

        return $this->applyCriteria($criteria, $all, PostConfiguration::class);
    }

    /**
     * A case insensitive search for a single post with an id. The first found post is returned.
     *
     * @param string $id
     * @return PostConfiguration|null
     */
    public function getOneById(string $id): ?PostConfiguration
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?PostConfiguration $carry, PostConfiguration $one) use ($id): ?PostConfiguration {
            if (null === $carry && strtolower($id) === strtolower($one->getId())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(PostConfiguration $postConfiguration): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(PostConfiguration $postConfiguration): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * Provision some data to save typing it into the file by hand. Just for those of us, who are lazy, you know.
     *
     * TODO: By now this logic gotten far to extensive to keep it in here for long. We should put this in some extra service.
     *
     * @param array $data
     * @return array
     */
    private function provisionIntersectingData(array $data): array
    {
        // Pull the locale data from the node.
        foreach ($data[PostConfigurationMapper::KEY_LOCALE_LIST] as $locale => $localeData) {
            $localeData = $this->provisionDefaultData($locale, $localeData);

            // Always set the id of the super page.
            $localeData[PostConfigurationLocaleMapper::KEY_ID] = $data[PostConfigurationMapper::KEY_ID];
            // Always set the origin to this repository for later content resolution.
            $localeData[PostConfigurationLocaleMapper::KEY_ORIGIN] = PostConfigurationRepository::class;

            // Set the locale to the one defined by the key, if not already set or not equal.
            if (!isset($localeData[PostConfigurationLocaleMapper::KEY_LOCALE])
                || $locale !== $localeData[PostConfigurationLocaleMapper::KEY_LOCALE]) {
                $localeData[PostConfigurationLocaleMapper::KEY_LOCALE] = $locale;
            }

            // Set the status to 'draft' when no status is set or the set status is not valid.
            if (!isset($localeData[PostConfigurationLocaleMapper::KEY_STATUS])
                || !in_array($localeData[PostConfigurationLocaleMapper::KEY_STATUS],
                    PostConfigurationLocaleMapper::STATUS_VALID, true)) {
                $localeData[PostConfigurationLocaleMapper::KEY_STATUS] = PostConfigurationLocale::STATUS_DRAFT;
            }

            // Set the slug to a properly formed slug.
            if (isset($localeData[PostConfigurationLocaleMapper::KEY_SLUG])) {
                $localeData[PostConfigurationLocaleMapper::KEY_SLUG] =
                    Slug::sanitize($localeData[PostConfigurationLocaleMapper::KEY_SLUG]);
            }

            // Provision the category list from an array of string or array to an array of array when not empty.
            if (isset($localeData[PostConfigurationLocaleMapper::KEY_CATEGORY_LIST])
                && !empty($localeData[PostConfigurationLocaleMapper::KEY_CATEGORY_LIST])) {
                $localeData[PostConfigurationLocaleMapper::KEY_CATEGORY_LIST] =
                    $this->provisionCategoryData($localeData[PostConfigurationLocaleMapper::KEY_CATEGORY_LIST]);
            }

            // Provision the tag list from an array of string or array to an array of array when not empty.
            if (isset($localeData[PostConfigurationLocaleMapper::KEY_TAG_LIST])
                && !empty($localeData[PostConfigurationLocaleMapper::KEY_TAG_LIST])) {
                $localeData[PostConfigurationLocaleMapper::KEY_TAG_LIST] =
                    $this->provisionTagData($localeData[PostConfigurationLocaleMapper::KEY_TAG_LIST]);
            }

            // Set the template to the one set in the super page if defined there but not inside the locale data.
            if (!isset($localeData[PostConfigurationLocaleMapper::KEY_TEMPLATE])
                && isset($data[PostConfigurationLocaleMapper::KEY_TEMPLATE])) {
                $localeData[PostConfigurationLocaleMapper::KEY_TEMPLATE] =
                    $data[PostConfigurationLocaleMapper::KEY_TEMPLATE];
            }

            // Push it back into the node.
            $data[PostConfigurationMapper::KEY_LOCALE_LIST][$locale] = $localeData;
        }

        return $data;
    }

    /**
     * @param string $locale
     * @param array $localeData
     * @return array
     */
    private function provisionDefaultData(string $locale, array $localeData): array
    {
        if (null !== ($defaultData = $this->defaultData['locale'][$locale] ?? null)) {
            $localeData = array_replace_recursive($defaultData, $localeData);
        }

        return $localeData;
    }

    /**
     * @param array|string[]|array[] $categoryList
     * @return array
     */
    private function provisionCategoryData(array $categoryList): array
    {
        // Pull the category data from the list.
        foreach ($categoryList as $index => $category) {
            $categoryObject = null;

            // Check if it is an array.
            if (is_array($category)) {
                try {
                    // Assume the array contains category data.
                    $categoryObject = $this->categoryMapper->fromData($category);
                } catch (FailedOperationException $exception) {
                    $this->logger->error('Unable to map category data to a valid object. This is likely caused by some malformed format.', [
                        'index' => $index,
                        'category' => $category,
                        'exception' => $exception,
                    ]);
                }

                // If it does not, the object stays with a null value.
            }

            // Check if it is a string.
            if (is_string($category)) {
                // Assume that string is the id of a category and get that category data from the repository.
                $categoryObject = $this->categoryRepository
                    ->getOneById($category);

                // If there was no category found above, just create a new category with identical id and name.
                if (null === $categoryObject) {
                    $categoryObject = (new Category())
                        ->setId($category)
                        ->setName($category);
                }
            }

            if (null === $categoryObject) {
                $this->logger->notice('Failed to provision category data.', [
                    'index' => $index,
                    'category' => $category,
                ]);

                // Unset the index if the category data could not be provisioned.
                unset($categoryList[$index]);
            } else {
                // Push it back into the list.
                $categoryList[$index] = $this->categoryMapper->toData($categoryObject);
            }
        }

        return $categoryList;
    }

    /**
     * @param array $tagList
     * @return array
     */
    private function provisionTagData(array $tagList): array
    {
        // Pull the tag data from the list.
        foreach ($tagList as $index => $tag) {
            $tagObject = null;

            // Check if it is an array.
            if (is_array($tag)) {
                try {
                    // Assume the array contains tag data.
                    $tagObject = $this->tagMapper->fromData($tag);
                } catch (FailedOperationException $exception) {
                    $this->logger->error('Unable to map tag data to a valid object. This is likely caused by some malformed format.', [
                        'index' => $index,
                        'tag' => $tag,
                        'exception' => $exception,
                    ]);
                }

                // If it does not, the object stays with a null value.
            }

            // Check if it is a string.
            if (is_string($tag)) {
                // Assume that string is the id of a tag and get that tag data from the repository.
                $tagObject = $this->tagRepository
                    ->getOneById($tag);

                // If there was no tag found above, just create a new tag with identical id and name.
                if (null === $tagObject) {
                    $tagObject = (new Tag())
                        ->setId($tag)
                        ->setName($tag);
                }
            }

            if (null === $tagObject) {
                $this->logger->notice('Failed to provision tag data.', [
                    'index' => $index,
                    'tag' => $tag,
                ]);

                // Unset the index if the tag data could not be provisioned.
                unset($tagList[$index]);
            } else {
                // Push it back into the list.
                $tagList[$index] = $this->tagMapper->toData($tagObject);
            }
        }

        return $tagList;
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getPostPath(string $entry): string
    {
        $path = $this->postService->getPath();

        return Path::join(...[
            $path,
            $entry,
        ]);
    }

    /**
     * @param string $entry
     * @return string
     */
    private function getConfigurationPath(string $entry): string
    {
        $path = $this->getPostPath($entry);

        return Path::join(...[
            $path,
            PostService::PATH_CONFIGURATION,
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
