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

/**
 * Class PostConfigurationMeta
 *
 * @package Made\Blog\Engine\Model\Configuration\Post
 */
class PostConfigurationMeta
{
    /**
     * @var string|null
     */
    private $author;

    /**
     * @var string|null
     */
    private $publisher;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var array|string[]|null
     */
    private $keywords;

    /**
     * @var string|null
     */
    private $robots;

    /**
     * @var array|PostConfigurationMetaCustom[]|null
     */
    private $customMeta;

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string|null $author
     * @return PostConfigurationMeta
     */
    public function setAuthor(?string $author): PostConfigurationMeta
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    /**
     * @param string|null $publisher
     * @return PostConfigurationMeta
     */
    public function setPublisher(?string $publisher): PostConfigurationMeta
    {
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return PostConfigurationMeta
     */
    public function setDescription(?string $description): PostConfigurationMeta
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return array|string[]|null
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param array|string[]|null $keywords
     * @return PostConfigurationMeta
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRobots(): ?string
    {
        return $this->robots;
    }

    /**
     * @param string|null $robots
     * @return PostConfigurationMeta
     */
    public function setRobots(?string $robots): PostConfigurationMeta
    {
        $this->robots = $robots;
        return $this;
    }

    /**
     * @return array|PostConfigurationMetaCustom[]|null
     */
    public function getCustomMeta(): ?array
    {
        return $this->customMeta;
    }

    /**
     * @param array|PostConfigurationMetaCustom[]|null $customMeta
     * @return PostConfigurationMeta
     */
    public function setCustomMeta(?array $customMeta): PostConfigurationMeta
    {
        $this->customMeta = $customMeta;
        return $this;
    }
}
