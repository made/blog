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
     * @var DateTime
     */
    private $postDate;

    /**
     * @var LocaleConfiguration[]
     */
    private $locale;

    /**
     * @var bool
     */
    private $status;

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

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     * @return PostConfiguration
     */
    public function setStatus(bool $status): PostConfiguration
    {
        $this->status = $status;
        return $this;
    }
}
