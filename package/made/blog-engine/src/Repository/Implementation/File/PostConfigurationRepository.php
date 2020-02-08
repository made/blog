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

use DateTime;
use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Help\Directory;
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Made\Blog\Engine\Service\PostConfigurationService;
use Made\Blog\Engine\Util\Sorter\PostConfigurationSorter;
use Psr\Log\LoggerInterface;

class PostConfigurationRepository implements PostConfigurationRepositoryInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PostConfigurationMapper
     */
    private $postConfigurationMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostConfigurationRepository constructor.
     * @param Configuration $configuration
     * @param PostConfigurationMapper $postConfigurationMapper
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, PostConfigurationMapper $postConfigurationMapper, ?LoggerInterface $logger)
    {
        // ToDo: Inject the default locale
        $this->configuration = $configuration;
        $this->postConfigurationMapper = $postConfigurationMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $path = $this->getPath();

        $list = Directory::listCallback($path, function (string $entry): bool {
            // ToDo: Maybe put this directly in the Directory helper class?
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            $postFolderPath = $this->getPostPath($entry);
            $postConfigurationFilePath = $this->getConfigurationPath($entry);

            if (!is_dir($postFolderPath) && !is_file($postConfigurationFilePath)) {
                // ToDo: Throwing an exception here will interrupt the flow, perfect would be logging.
//                throw new PostConfigurationException('Something ain`t right with the configuration for this blog post, sir.', [
//                    'post_folder_path' => $postFolderPath,
//                    'post_configuration_file_path' => $postConfigurationFilePath,
//                ]);
                return false;
            }

            return true;
        });

        $all = array_map(function (string $entry): ?PostConfiguration {
            $configurationPath = $this->getConfigurationPath($entry);
            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            $data[PostConfigurationMapper::KEY_PATH] = $this->getPostPath($entry);

            try {
                return $this->postConfigurationMapper->fromData($data);
            } catch (PostConfigurationException $exception) {
                throw $exception;
            }

        }, $list);

        $filteredResult = array_filter($all);
        return PostConfigurationSorter::sortByPostDate($filteredResult);
    }

    /**
     * A case insensitive search for a single post with an id. The first found post is returned.
     * @param string $id
     * @return PostConfiguration|null
     */
    public function getOneById(string $id)
    {
        $all = $this->getAll();

        foreach ($all as $post) {
            if (strtolower($id) === strtolower($post->getPostId())) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Get all the posts by a specific date.
     * @param DateTime $dateTime
     * @return array
     */
    public function getAllByPostDate(DateTime $dateTime)
    {
        $all = $this->getAll();

        return array_filter($all, function ($postConfiguration) use ($dateTime) {
            /** @var PostConfiguration $postConfiguration */
            if ($postConfiguration->getPostDate()->format('Ymd') === $dateTime->format('Ymd')) {
                var_dump($dateTime);
                return true;
            }
            return false;
        });
    }

    /**
     * Gets all posts by status (case insensitive)
     * @param mixed ...$status
     * @return array
     */
    public function getAllByStatus(...$status)
    {
        $all = $this->getAll();
        $cleanedStatus = [];
        // Flatten the input to a single array
        array_walk_recursive($status, function ($a) use (&$cleanedStatus) {
            $cleanedStatus[] = strtolower($a);
        });

        return array_filter($all, function ($postConfiguration) use ($cleanedStatus) {
            /** @var PostConfiguration $postConfiguration */
            return in_array(strtolower($postConfiguration->getStatus()), $cleanedStatus);
        });
    }

    /**
     * Gets the Path for the folder containing all blog entries
     * @return string
     */
    private function getPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            PostConfigurationService::PATH_POSTS,
        ]);
    }


    /**
     * Gets the Path for the given blog post
     * @param string $entry
     * @return string
     */
    private function getPostPath(string $entry): string
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
    private function getConfigurationPath(string $entry): string
    {
        $path = $this->getPostPath($entry);

        return Path::join(...[
            $path,
            PostConfigurationService::PATH_CONFIGURATION,
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
