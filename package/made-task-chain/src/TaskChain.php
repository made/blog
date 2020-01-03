<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 GameplayJDK
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

namespace Made\TaskChain;

use Closure;

/**
 * Class TaskChain
 *
 * @package Made\TaskChain
 */
class TaskChain implements TaskChainInterface
{
    /**
     * @var int
     */
    protected $current;

    /**
     * @var array
     */
    protected $collection;

    /**
     * @var bool
     */
    protected $lock;

    /**
     * @var bool
     */
    protected $throw;

    /**
     * TaskChain constructor.
     * @param bool $throw
     */
    public function __construct(bool $throw = false)
    {
        $this->current = -1;
        $this->collection = [];
        $this->lock = false;
        $this->throw = $throw;
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return !empty($this->collection);
    }

    /**
     * @inheritDoc
     * @throws TaskException
     */
    public function process($input, callable $nextCallback)
    {
        $this->collection = $this->getFinalCollection();

        $this->lock = true;

        $callback = $this->createNextCallback();
        $input = $callback($input);

        $this->current = -1;
        $this->lock = false;

        return $nextCallback($input);
    }

    /**
     * @return Closure
     */
    protected function createNextCallback()
    {
        $this->current++;

        $task = null;

        // Only set if the end of the array has not yet been reached.
        if (count($this->collection) > $this->current) {
            $task = $this->collection[$this->current];
        } else {
            /**
             * @param mixed $input
             * @return mixed
             */
            return function ($input) {
                return $input;
            };
        }

        /**
         * @param mixed $input
         * @return mixed
         */
        return function ($input) use ($task) {
            /** @var null|TaskInterface $task */

            if (null === $task) {
                return $input;
            }

            if ($task->accept($input)) {
                return $task->process($input, $this->createNextCallback());
            }

            if (true === $this->throw) {
                throw new TaskException('Task did not accept input.');
            }

            // Skip if graceful.
            $callback = $this->createNextCallback();
            return $callback($input);
        };
    }

    /**
     * @param mixed $input
     * @return mixed
     * @throws TaskException
     */
    public function run($input)
    {
        /**
         * @param mixed $input
         * @return mixed
         */
        $callback = function ($input) {
            return $input;
        };

        return $this->process($input, $callback);
    }

    /**
     * @param TaskInterface $task
     * @param int $priority
     * @return TaskChain
     * @throws TaskException
     */
    public function add(TaskInterface $task, int $priority = -1): TaskChainInterface
    {
        if (true === $this->lock) {
            throw new TaskException('Task chain is locked.');
        }

        // No negative priority.
        if (0 > $priority) {
            $this->collection[] = $task;
        } else {
            while (array_key_exists($priority, $this->collection)) {
                echo $priority;
                $priority++;
            }

            $this->collection[$priority] = $task;
        }

        return $this;
    }

    /**
     * @return array
     * @throws TaskException
     */
    protected function getFinalCollection(): array
    {
        $collection = $this->collection;

        if (!ksort($collection, SORT_NUMERIC)) {
            throw new TaskException('Task collection asort() failed.');
        }

        return array_values($collection);
    }
}
