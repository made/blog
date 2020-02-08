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

class MetaConfiguration
{
    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $publisher;

    /**
     * @var string
     */
    private $robots;

    /**
     * @var MetaCustomElementConfiguration[]
     */
    private $custom;

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     * @return MetaConfiguration
     */
    public function setKeywords(string $keywords): MetaConfiguration
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return MetaConfiguration
     */
    public function setAuthor(string $author): MetaConfiguration
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublisher(): string
    {
        return $this->publisher;
    }

    /**
     * @param string $publisher
     * @return MetaConfiguration
     */
    public function setPublisher(string $publisher): MetaConfiguration
    {
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * @return string
     */
    public function getRobots(): string
    {
        return $this->robots;
    }

    /**
     * @param string $robots
     * @return MetaConfiguration
     */
    public function setRobots(string $robots): MetaConfiguration
    {
        $this->robots = $robots;
        return $this;
    }

    /**
     * @return MetaCustomElementConfiguration[]
     */
    public function getCustom(): array
    {
        return $this->custom;
    }

    /**
     * @param MetaCustomElementConfiguration[] $custom
     * @return MetaConfiguration
     */
    public function setCustom(array $custom): MetaConfiguration
    {
        $this->custom = $custom;
        return $this;
    }
}
