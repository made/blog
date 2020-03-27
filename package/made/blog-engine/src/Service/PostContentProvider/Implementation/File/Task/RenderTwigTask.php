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
use Made\Blog\Engine\Service\PostService;
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class RenderTwigTask
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class RenderTwigTask extends TaskAbstract
{
    const ALIAS_CONTEXT = 'context';

    /**
     * @var PostService
     */
    private $postService;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RenderTwigTask constructor.
     * @param int $priority
     * @param PostService $postService
     * @param Environment $environment
     * @param LoggerInterface $logger
     */
    public function __construct(int $priority, PostService $postService, Environment $environment, LoggerInterface $logger)
    {
        parent::__construct($priority);

        $this->postService = $postService;
        $this->environment = $environment;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return is_array($input)
            && ($input[PostConfigurationLocale::class] ?? null) instanceof PostConfigurationLocale
            && ($input[PostContent::class] ?? null) instanceof PostContent
            && is_array($input[static::ALIAS_CONTEXT] ?? null);
    }

    /**
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        /** @var PostConfigurationLocale $postConfigurationLocale */
        $postConfigurationLocale = $input[PostConfigurationLocale::class];
        /** @var PostContent $postContent */
        $postContent = $input[PostContent::class];
        /** @var array $context */
        $context = $input[static::ALIAS_CONTEXT];

        $path = $this->postService->getNamespacePath($postConfigurationLocale->getId());
        $content = $postContent->getContent();

        try {
            $content = $this->environment
                ->render($path, $context);
        } catch (LoaderError | RuntimeError | SyntaxError $error) {
            $this->logger->error('Error on twig render: ' . $error->getRawMessage(), [
                'error', $error,
            ]);
        }

        // As the content is an object it has the same instance in the further input and does not require to be updated there.
        $postContent->setContent($content);

        return $nextCallback($input);
    }
}
