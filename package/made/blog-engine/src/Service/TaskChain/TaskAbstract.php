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

namespace Made\Blog\Engine\Service\TaskChain;

use TaskChain\TaskInterface;

/**
 * Class TaskAbstract
 *
 * @package Made\Blog\Engine\Service\TaskChain
 */
abstract class TaskAbstract implements TaskInterface
{
    /**
     * @var int
     */
    private $priority;

    /**
     * TaskAbstract constructor.
     * @param int $priority
     */
    public function __construct(int $priority = -1)
    {
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    public abstract function accept($input): bool;

    /**
     * @inheritDoc
     */
    public abstract function process($input, callable $nextCallback);

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return TaskAbstract
     */
    public function setPriority(int $priority): TaskAbstract
    {
        $this->priority = $priority;
        return $this;
    }
}
