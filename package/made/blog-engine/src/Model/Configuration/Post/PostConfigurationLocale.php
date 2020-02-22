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

/**
 * Class PostConfigurationLocale
 *
 * @package Made\Blog\Engine\Model\Configuration\Post
 */
class PostConfigurationLocale
{
    /**
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
    private $locale;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var PostConfigurationMeta
     */
    private $meta;

    /**
     * @var array|string[]
     */
    private $categories;

    /**
     * @var array|string[]
     */
    private $tags;

    /**
     * @var array|string[]
     */
    private $redirects;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PostConfigurationLocale
     */
    public function setId(string $id): PostConfigurationLocale
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
     * @return PostConfigurationLocale
     */
    public function setStatus(string $status): PostConfigurationLocale
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return PostConfigurationLocale
     */
    public function setLocale(string $locale): PostConfigurationLocale
    {
        $this->locale = $locale;
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
     * @return PostConfigurationLocale
     */
    public function setDate(DateTime $date): PostConfigurationLocale
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return PostConfigurationLocale
     */
    public function setSlug(string $slug): PostConfigurationLocale
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return PostConfigurationLocale
     */
    public function setTitle(string $title): PostConfigurationLocale
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PostConfigurationLocale
     */
    public function setDescription(string $description): PostConfigurationLocale
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return PostConfigurationMeta
     */
    public function getMeta(): PostConfigurationMeta
    {
        return $this->meta;
    }

    /**
     * @param PostConfigurationMeta $meta
     * @return PostConfigurationLocale
     */
    public function setMeta(PostConfigurationMeta $meta): PostConfigurationLocale
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array|string[] $categories
     * @return PostConfigurationLocale
     */
    public function setCategories(array $categories): PostConfigurationLocale
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array|string[] $tags
     * @return PostConfigurationLocale
     */
    public function setTags(array $tags): PostConfigurationLocale
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getRedirects(): array
    {
        return $this->redirects;
    }

    /**
     * @param array|string[] $redirects
     * @return PostConfigurationLocale
     */
    public function setRedirects(array $redirects): PostConfigurationLocale
    {
        $this->redirects = $redirects;
        return $this;
    }
}
