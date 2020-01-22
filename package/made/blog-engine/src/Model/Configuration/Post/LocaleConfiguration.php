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

class LocaleConfiguration
{
    /**
     * @var string
     */
    private $language;

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
     * @var MetaConfiguration
     */
    private $meta;

    /**
     * @var array
     */
    private $categories;

    /**
     * @var array
     */
    private $tags;

    /**
     * @var array
     */
    private $redirect;

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return LocaleConfiguration
     */
    public function setLanguage(string $language): LocaleConfiguration
    {
        $this->language = $language;
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
     * @return LocaleConfiguration
     */
    public function setSlug(string $slug): LocaleConfiguration
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
     * @return LocaleConfiguration
     */
    public function setTitle(string $title): LocaleConfiguration
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
     * @return LocaleConfiguration
     */
    public function setDescription(string $description): LocaleConfiguration
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return MetaConfiguration
     */
    public function getMeta(): MetaConfiguration
    {
        return $this->meta;
    }

    /**
     * @param MetaConfiguration $meta
     * @return LocaleConfiguration
     */
    public function setMeta(MetaConfiguration $meta): LocaleConfiguration
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return LocaleConfiguration
     */
    public function setCategories(array $categories): LocaleConfiguration
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return LocaleConfiguration
     */
    public function setTags(array $tags): LocaleConfiguration
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return array
     */
    public function getRedirect(): array
    {
        return $this->redirect;
    }

    /**
     * @param array $redirect
     * @return LocaleConfiguration
     */
    public function setRedirect(array $redirect): LocaleConfiguration
    {
        $this->redirect = $redirect;
        return $this;
    }
}
