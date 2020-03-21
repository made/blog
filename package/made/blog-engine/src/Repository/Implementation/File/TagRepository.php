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
use Made\Blog\Engine\Help\File;
use Made\Blog\Engine\Help\Json;
use Made\Blog\Engine\Help\Path;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\Tag;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\TagMapper;
use Made\Blog\Engine\Repository\TagRepositoryInterface;
use Made\Blog\Engine\Service\PostService;
use Psr\Log\LoggerInterface;

/**
 * Class TagRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class TagRepository implements TagRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var TagMapper
     */
    private $tagMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TagRepository constructor.
     * @param Configuration $configuration
     * @param TagMapper $tagMapper
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, TagMapper $tagMapper, LoggerInterface $logger)
    {
        $this->configuration = $configuration;
        $this->tagMapper = $tagMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Tag $tag): bool
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

        if (is_file($configurationPath)) {
            // Even if the tag configuration file does not exist, this will return an empty array.
            $list = $this->getContent($configurationPath);
        } else {
            // TODO: Logging.
        }

        /** @var array|Tag[] $all */
        $all = array_map(function (array $data): ?Tag {
            if (empty($data)) {
                return null;
            }

            try {
                return $this->tagMapper->fromData($data);
            } catch (FailedOperationException $exception) {
                // TODO: Logging.
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?Tag $tag): bool {
            return null !== $tag;
        });

        return $this->applyCriteria($criteria, $all, Tag::class);
    }

    /**
     * @inheritDoc
     */
    public function getOneById(string $id): ?Tag
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Tag $carry, Tag $one) use ($id): ?Tag {
            if (null === $carry && strtolower($id) === strtolower($one->getId())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Tag
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Tag $carry, Tag $one) use ($name): ?Tag {
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
    public function modify(Tag $tag): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Tag $tag): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }


    /**
     * TODO: Use PostService::getPath() instead.
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
            PostService::PATH_CONFIGURATION_TAG,
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
