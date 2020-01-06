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

namespace Made\Blog\Engine\Service\Configuration;

use Made\Blog\Engine\Exception\ConfigurationException;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Service\Configuration\Strategy\ConfigurationStrategyInterface;

/**
 * Class ConfigurationService
 *
 * @package Made\Blog\Engine\Service\Configuration
 */
class ConfigurationService
{
    /**
     * @var ConfigurationStrategyInterface
     */
    private $configurationStrategy;

    /**
     * ConfigurationService constructor.
     * @param ConfigurationStrategyInterface $configurationStrategy
     */
    public function __construct(ConfigurationStrategyInterface $configurationStrategy)
    {
        $this->configurationStrategy = $configurationStrategy;
    }

    /**
     * @param bool $shouldThrow If an exception should be thrown for an empty configuration array returned by the strategy.
     * @return array
     * @throws ConfigurationException
     */
    public function getConfigurationArray(bool $shouldThrow = false): array
    {
        $content = $this->configurationStrategy->initialize();

        if (empty($content) && $shouldThrow) {
            throw new ConfigurationException('Empty configuration initialized!');
        }

        return $content;
    }
}
