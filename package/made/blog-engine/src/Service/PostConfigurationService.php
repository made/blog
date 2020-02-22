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

namespace Made\Blog\Engine\Service;

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\PostConfigurationRepositoryInterface;

class PostConfigurationService
{
    /**
     * ToDo: Make this configurable.
     * @var string
     */
    const PATH_POSTS = '/posts';

    /**
     * ToDo: Make this configurable later.
     * Name of the configuration file which is needed for each blog post
     *
     * @var string
     */
    const PATH_CONFIGURATION = 'configuration.json';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PostConfigurationRepositoryInterface
     */
    private $postConfigurationRepository;

    /**
     * PostConfigurationService constructor.
     * @param Configuration $configuration
     * @param PostConfigurationRepositoryInterface $postConfigurationRepository
     */
    public function __construct(Configuration $configuration, PostConfigurationRepositoryInterface $postConfigurationRepository)
    {
        $this->configuration = $configuration;
        $this->postConfigurationRepository = $postConfigurationRepository;
    }
}