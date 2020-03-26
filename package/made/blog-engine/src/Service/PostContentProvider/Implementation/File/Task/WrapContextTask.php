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

namespace Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task;

use Made\Blog\Engine\Model\PostConfigurationLocale;
use Made\Blog\Engine\Model\PostContent;
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;

/**
 * Class WrapContextTask
 *
 * This task is designed to wrap the twig context for the later task. To add additional data, just create a new task
 * with a lower priority, which then can add or modify the context, as long as it is executed after this task and before
 * the next one ({@see RenderTwigTask}).
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class WrapContextTask extends TaskAbstract
{
    const ALIAS_CONTEXT = RenderTwigTask::ALIAS_CONTEXT;

    /**
     * WrapDataTask constructor.
     * @param int $priority
     */
    public function __construct(int $priority)
    {
        parent::__construct($priority);
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return is_array($input)
            && ($input[PostConfigurationLocale::class] ?? null) instanceof PostConfigurationLocale
            && ($input[PostContent::class] ?? null) instanceof PostContent;
    }

    /**
     * @var array $input
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        /** @var PostConfigurationLocale $postConfigurationLocale */
        $postConfigurationLocale = $input[PostConfigurationLocale::class];

        $context = [
            'configuration' => $postConfigurationLocale,
        ];

        // Put the context into the further input.
        $input[static::ALIAS_CONTEXT] = $context;

        /** @var array $output */
        $output = $nextCallback($input);

        // Clean up after ourselves in the output.
        unset($output[static::ALIAS_CONTEXT]);

        return $output;
    }
}
