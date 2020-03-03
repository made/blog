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
use Parsedown;

/**
 * Class RenderParsedownTask
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class RenderParsedownTask extends TaskAbstract
{
    /**
     * @var Parsedown
     */
    private $parsedown;

    /**
     * RenderParsedownTask constructor.
     * @param int $priority
     * @param Parsedown $parsedown
     */
    public function __construct(int $priority, Parsedown $parsedown)
    {
        parent::__construct($priority);

        $this->parsedown = $parsedown;
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
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        /** @var PostContent $postContent */
        $postContent = $input[PostContent::class];

        $content = $postContent->getContent();

        if (!empty($content)) {
            // This will also work with parsedown-extra installed.
            $content = $this->parsedown->text($content);
        }

        $postContent->setContent($content);

        return $nextCallback($input);
    }
}
