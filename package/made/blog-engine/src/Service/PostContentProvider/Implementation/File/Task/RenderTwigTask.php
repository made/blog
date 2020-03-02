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
use Twig\Environment;

/**
 * Class RenderTwigTask
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class RenderTwigTask extends TaskAbstract
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * RenderTwigTask constructor.
     * @param int $priority
     * @param Environment $environment
     */
    public function __construct(int $priority, Environment $environment)
    {
        parent::__construct($priority);

        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return is_array($input);
    }

    /**
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        /** @var PostConfigurationLocale $postConfigurationLocale */
        $postConfigurationLocale = $input[PostConfigurationLocale::class];

        // TODO: Resolve path correctly. This should not be done exclusively in this task class, but inside a dedicated
        //  service class. That would enable different types of format loading depending on which one is configured. As
        //  of now this would not work as the id is set to the full path except of the last path segment denoting the
        //  post folder name, which will be needed here, since "@Post" is the namespace of the "/posts" folder or
        //  whatever path is configured for that.
        //  See "/package/made/blog-engine/src/Repository/Implementation/File/PostConfigurationRepository.php".
        $path = "@Post/{$postConfigurationLocale->getId()}/content.md.twig";

        // TODO: Handle errors.
        $content = $this->environment->render($path, $input);

        $input[PostContent::class] = (new PostContent())
            ->setContent($content);

        return $nextCallback($input);
    }
}
