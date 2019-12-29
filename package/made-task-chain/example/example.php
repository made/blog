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
 *
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Made\TaskChain\TaskChain;
use Made\TaskChain\TaskInterface;

$c = new TaskChain();

/**
 * Class TaskAdd
 */
class TaskAdd implements TaskInterface {

    /**
     * @var int
     */
    private $number;

    /**
     * TaskAdd constructor.
     * @param int $number
     */
    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return is_int($input);
    }

    /**
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        echo print_r($this, true);
        echo PHP_EOL;

        return $nextCallback($input + $this->number);
    }
}

/**
 * Class TaskMul
 */
class TaskMul implements TaskInterface {

    /**
     * @var int
     */
    private $number;

    /**
     * TaskMul constructor.
     * @param int $number
     */
    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @inheritDoc
     */
    public function accept($input): bool
    {
        return is_int($input);
    }

    /**
     * @inheritDoc
     */
    public function process($input, callable $nextCallback)
    {
        echo print_r($this, true);
        echo PHP_EOL;

        return $nextCallback($input * $this->number);
    }
}

$c->add(new TaskAdd(1), 2);
$c->add(new TaskMul(2), 3);

echo $c->run(1);
echo PHP_EOL;
