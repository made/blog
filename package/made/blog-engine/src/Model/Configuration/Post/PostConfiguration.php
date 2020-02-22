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

namespace Made\Blog\Engine\Model\Configuration\Post;

use DateTime;

class PostConfiguration
{
    /**
     * info: will be the folder name for the file implementation, database will be an ID
     *
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $path;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var array|LocaleConfiguration[]
     */
    private $locale;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PostConfiguration
     */
    public function setId(string $id): PostConfiguration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return PostConfiguration
     */
    public function setStatus(string $status): PostConfiguration
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return PostConfiguration
     */
    public function setPath(string $path): PostConfiguration
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return PostConfiguration
     */
    public function setDate(DateTime $date): PostConfiguration
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return array|LocaleConfiguration[]
     */
    public function getLocale(): array
    {
        return $this->locale;
    }

    /**
     * @param array|LocaleConfiguration[] $locale
     * @return PostConfiguration
     */
    public function setLocale(array $locale): PostConfiguration
    {
        $this->locale = $locale;
        return $this;
    }
}