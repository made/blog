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
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Model\PostConfiguration;
use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationLocaleMapper;
use Made\Blog\Engine\Repository\Mapper\PostConfigurationMapper;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PostConfigurationRepository
 *
 * @TODO Formatting is really messed up... please fix it.
 * @TODO Tidy up the docblocks, use more inheritdoc and put the docblocks into the interfaces, where they belong.
 * @package Made\Blog\Engine\Repository\Implementation\File
 */
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
    public function __construct(
        Configuration $configuration,
        PostConfigurationMapper $postConfigurationMapper,
        LoggerInterface $logger
    )
    {
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

        /** @var array|string[] $list */
        $list = Directory::listCallback($path, function (string $entry): bool {
            if ('.' === $entry || '..' === $entry) {
                return false;
            }

            $postPath = $this->getPostPath($entry);
            $configurationPath = $this->getConfigurationPath($entry);

            // TODO: Logging.
            return is_dir($postPath) && is_file($configurationPath);
        });

        /** @var array|PostConfiguration[] $all */
        $all = array_map(function (string $entry): ?PostConfiguration {
            $configurationPath = $this->getConfigurationPath($entry);
            $data = $this->getContent($configurationPath);

            if (empty($data)) {
                return null;
            }

            $data[PostConfigurationMapper::KEY_ID] = $this->getPostPath($entry);
            $data = $this->provisionIntersectingData($data);

            try {
                return $this->postConfigurationMapper->fromData($data);
            } catch (MapperException $exception) {
                // TODO: Logging.
            }

            return null;
        }, $list);

        return array_filter($all, function (?PostConfiguration $postConfiguration): bool {
            return null !== $postConfiguration;
        });
    }

    /**
     * A case insensitive search for a single post with an id. The first found post is returned.
     *
     * @param string $id
     * @return \Made\Blog\Engine\Model\PostConfiguration|null
     */
    public function getOneById(string $id): ?PostConfiguration
    {
        $all = $this->getAll();

        return array_reduce($all, function (?PostConfiguration $carry, PostConfiguration $one) use ($id): ?PostConfiguration {
            if (null === $carry && strtolower($id) === strtolower($one->getId())) {
                return $one;
            }

            return $carry;
        }, null);
    }

    /**
     * Provision some data to save typing it into the file by hand. Just for those of us, who are lazy, you know.
     *
     * @param array $data
     * @return array
     */
    private function provisionIntersectingData(array $data): array
    {
        // Pull the locale data from the node.
        foreach ($data[PostConfigurationMapper::KEY_LOCALE_LIST] as $locale => $localeData) {
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

            // INFO: Maybe some more? Lemme know.

            // Pull it back into the node.
            $data[PostConfigurationMapper::KEY_LOCALE_LIST][$locale] = $localeData;
        }

        return $data;
    }

    /**
     * Gets the Path for the folder containing all blog entries
     * @return string
     */
    private function getPath(): string
    {
        return Path::join(...[
            $this->configuration->getRootDirectory(),
            // TODO: Do it like the theme service did.
            static::POST_PATH,
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
