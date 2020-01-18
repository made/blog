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

namespace Made\Blog\Engine\Repository\Mapper;

use Made\Blog\Engine\Exception\ContentException;
use Made\Blog\Engine\Model\Content;

class ContentMapper
{
    const KEY_SLUG = 'slug';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_LOCALE = 'locale';
    const KEY_CATEGORIES = 'categories';
    const KEY_TAGS = 'tags';
    const KEY_REDIRECT = 'redirect';

    /**
     * @param array $data
     * @return Content
     * @throws ContentException
     */
    public function fromData(array $data)
    {
        $content = new Content();

        if (!array_key_exists(static::KEY_SLUG, $data)) {
            throw new ContentException('Unfortunately no slug is configured.');
        }

        if (!array_key_exists(static::KEY_TITLE, $data)) {
            throw new ContentException('Unfortunately no title is configured.');
        }

        if (!array_key_exists(static::KEY_DESCRIPTION, $data)) {
            throw new ContentException('Unfortunately no description is configured.');
        }

        if (!array_key_exists(static::KEY_LOCALE, $data)) {
            throw new ContentException('Unfortunately no description is configured.');
        }

        $content
            ->setSlug($data[static::KEY_SLUG])
            ->setTitle($data[static::KEY_TITLE])
            ->setDescription($data[static::KEY_DESCRIPTION]);

        if (array_key_exists(static::KEY_CATEGORIES, $data)) {
            $content->setCategories($data[static::KEY_CATEGORIES]);
        }

        if (array_key_exists(static::KEY_TAGS, $data)) {
            $content->setTags($data[static::KEY_TAGS]);
        }

        if (array_key_exists(static::KEY_REDIRECT, $data)) {
            $content->setRedirect($data[static::KEY_REDIRECT]);
        }

        return $content;
    }
}
