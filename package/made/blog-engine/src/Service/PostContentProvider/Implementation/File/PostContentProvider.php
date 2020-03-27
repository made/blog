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

namespace Made\Blog\Engine\Service\PostContentProvider\Implementation\File;

use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\PostContent;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Engine\Service\PostContentProviderInterface;
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;
use TaskChain\TaskChainInterface;

/**
 * Class PostContentProvider
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File
 */
class PostContentProvider implements PostContentProviderInterface
{
    const TAG_POST_CONTENT_PROVIDER_TASK = 'provider.post_content.file.task';

    /**
     * @var TaskChainInterface
     */
    private $taskChain;

    /**
     * PostContentProvider constructor.
     * @param TaskChainInterface $taskChain
     * @param array|TaskAbstract[] $taskList
     */
    public function __construct(TaskChainInterface $taskChain, array $taskList)
    {
        $this->taskChain = $taskChain;

        foreach ($taskList as $task) {
            $this->taskChain->add($task, $task->getPriority());
        }
    }

    /**
     * @inheritDoc
     */
    public function accept(string $origin): bool
    {
        return PostConfigurationRepositoryFile::class === $origin
            // This is possible, since the current implementation of a task chain does not care about the input, but
            // instead only requires the contained task collection to be non-empty.
            && $this->taskChain->accept(null);
    }

    /**
     * @inheritDoc
     */
    public function provide(PostConfigurationLocale $postConfigurationLocale): ?PostContent
    {
        $postContent = (new PostContent())
            ->setContent('');

        $input = [
            PostConfigurationLocale::class => $postConfigurationLocale,
            PostContent::class => $postContent,
        ];

        $output = $this->taskChain->run($input);

        // Await a post content object as result.
        return ($result = ($output[PostContent::class] ?? null)) instanceof PostContent ? $result : null;
    }
}
