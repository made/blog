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
use Made\Blog\Engine\Exception\FailedOperationException;
use Made\Blog\Engine\Exception\UnsupportedOperationException;
use Made\Blog\Engine\Model\Author;
use Made\Blog\Engine\Repository\AuthorRepositoryInterface;
use Made\Blog\Engine\Repository\Criteria\Criteria;
use Made\Blog\Engine\Repository\Mapper\AuthorMapper;
use Made\Blog\Engine\Service\PathService;
use Psr\Log\LoggerInterface;

/**
 * Class AuthorRepository
 *
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
class AuthorRepository implements AuthorRepositoryInterface
{
    use CriteriaHelperTrait;

    /**
     * @var PathService
     */
    private $pathService;

    /**
     * @var AuthorMapper
     */
    private $authorMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AuthorRepository constructor.
     * @param PathService $pathService
     * @param AuthorMapper $authorMapper
     * @param LoggerInterface $logger
     */
    public function __construct(PathService $pathService, AuthorMapper $authorMapper, LoggerInterface $logger)
    {
        $this->pathService = $pathService;
        $this->authorMapper = $authorMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function create(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     */
    public function getAll(Criteria $criteria): array
    {
        $configurationPath = $this->pathService
            ->getPathAuthorConfiguration();

        $list = [];

        if (!is_readable($configurationPath)) {
            $this->logger->notice('Empty author file at configuration path.', [
                'configurationPath' => $configurationPath,
            ]);

            return $list;
        }

        // Even if the author configuration file would not exist, this will return an empty array.
        $list = $this->getContent($configurationPath);

        $list = array_filter($list, 'is_array');

        /** @var array|Author[] $all */
        $all = array_map(function (array $data): ?Author {
            if (empty($data)) {
                return null;
            }

            try {
                return $this->authorMapper
                    ->fromData($data);
            } catch (FailedOperationException $exception) {
                $this->logger->error('Unable to map author data to a valid object. This is likely caused by some malformed format.', [
                    'data' => $data,
                    'exception' => $exception,
                ]);
            }

            return null;
        }, $list);

        $all = array_filter($all, function (?Author $author): bool {
            return null !== $author;
        });

        return $this->applyCriteria($criteria, $all, Author::class);
    }

    /**
     * @inheritDoc
     */
    public function getAllByLocation(Criteria $criteria, string $location): array
    {
        $all = $this->getAll($criteria);

        return array_filter($all, function (Author $one) use ($location): bool {
            $oneLocation = $one->getLocation();

            // TODO: Refine search logic.
            return false === strpos($oneLocation, $location);
        });
    }

    /**
     * @inheritDoc
     */
    public function getOneByName(string $name): ?Author
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Author $carry, Author $one) use ($name): ?Author {
            if (null === $carry && strtolower($name) === strtolower($one->getName())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     */
    public function getOneByNameDisplay(string $nameDisplay): ?Author
    {
        $all = $this->getAll(new Criteria());

        return array_reduce($all, function (?Author $carry, Author $one) use ($nameDisplay): ?Author {
            if (null === $carry && strtolower($nameDisplay) === strtolower($one->getNameDisplay())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function modify(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
    }

    /**
     * @inheritDoc
     * @throws UnsupportedOperationException
     */
    public function destroy(Author $author): bool
    {
        throw new UnsupportedOperationException('Unsupported operation: ' . __METHOD__ . '! '
            . 'The file repository can not be used for that type of action.');
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
