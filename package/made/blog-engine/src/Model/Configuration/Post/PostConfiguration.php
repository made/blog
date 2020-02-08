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
use Made\Blog\Engine\Exception\PostConfigurationException;

class PostConfiguration
{
    public const ACCEPTED_STATUS = ['draft', 'review', 'publish'];

    /**
     * ToDo: will be the folder name for the file implementation, database will be an ID
     * @var string
     */
    private $postId;

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
    private $postDate;

    /**
     * @var LocaleConfiguration[]
     */
    private $locale;

    /**
     * @param string $status
     * @return PostConfiguration
     * @throws PostConfigurationException
     */
    public function setStatus(string $status): PostConfiguration
    {
        if (!in_array(strtolower($status), self::ACCEPTED_STATUS)) {
            throw new PostConfigurationException("status $status not valid.");
        }
        $this->status = $status;

        return $this;
    }


    // generated methods

    /**
     * @return string
     */
    public function getPostId(): string
    {
        return $this->postId;
    }

    /**
     * @param string $postId
     * @return PostConfiguration
     */
    public function setPostId(string $postId): PostConfiguration
    {
        $this->postId = $postId;
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
    public function getPostDate(): DateTime
    {
        return $this->postDate;
    }

    /**
     * @param DateTime $postDate
     * @return PostConfiguration
     */
    public function setPostDate(DateTime $postDate): PostConfiguration
    {
        $this->postDate = $postDate;
        return $this;
    }

    /**
     * @return LocaleConfiguration[]
     */
    public function getLocale(): array
    {
        return $this->locale;
    }

    /**
     * @param LocaleConfiguration[] $locale
     * @return PostConfiguration
     */
    public function setLocale(array $locale): PostConfiguration
    {
        $this->locale = $locale;
        return $this;
    }

}
