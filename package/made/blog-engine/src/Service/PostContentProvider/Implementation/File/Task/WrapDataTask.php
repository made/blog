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
use Made\Blog\Engine\Service\TaskChain\TaskAbstract;

/**
 * Class WrapDataTask
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class WrapDataTask extends TaskAbstract
{
    const ALIAS_CONFIGURATION = 'configuration';

    // TODO: Add possibility to influence what things are provided as input/output. This task class is dedicated to
    //  enrichment (and clean-up) of the input array, which will be given to twig as a rendering context later down the
    //  chain.

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
            && ($input[PostConfigurationLocale::class] ?? null) instanceof PostConfigurationLocale;
    }

    /**
     * @var array $input
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
//        /** @var PostConfigurationLocale $postConfigurationLocale */
//        $postConfigurationLocale = $input[PostConfigurationLocale::class];

        // TODO: Add possibility to influence this data.
        $input[static::ALIAS_CONFIGURATION] = $input[PostConfigurationLocale::class];

        /** @var array $output */
        $output = $nextCallback($input);

        // Clean up after ourselves.
        unset($output[static::ALIAS_CONFIGURATION]);

        return $output;
    }
}
