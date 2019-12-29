<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 Made
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

namespace Made\Blog\Engine;

use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Package\PackageAbstract;
use Pimple\Container;

/**
 * Class Package
 *
 * @package Made\Blog\Engine
 */
class Package extends PackageAbstract
{
    protected const SERVICE_NAME = 'engine';

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     * @throws Exception\PackageException
     */
    public function register(Container $pimple): void
    {
        if (!$this->hasConfigurationSupport($pimple)) {
            $this->addConfigurationSupport($pimple);
        }

        $this->registerConfiguration($pimple, static::SERVICE_NAME, [
            'theme' => 'theme-base',
        ]);

        $configuration = $pimple[static::SERVICE_NAME_CONFIGURATION];

        $this->registerService($pimple, static::SERVICE_NAME, function (Container $container) use ($configuration): Configuration {
            $settings = $configuration['engine'];
            return (new Configuration())
                ->setRootDirectory($configuration['root_directory'])
                ->setTheme($settings['theme']);
        });
    }
}
