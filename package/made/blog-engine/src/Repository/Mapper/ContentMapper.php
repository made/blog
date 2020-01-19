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
use Made\Blog\Engine\Model\Content\Content;
use Made\Blog\Engine\Model\Content\Locale;
use Made\Blog\Engine\Model\Content\Meta;

class ContentMapper
{
    const KEY_SLUG = 'slug';
    const KEY_POST_DATE = 'post_date';
    const KEY_STATUS = 'status';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_LOCALE = 'locale';
    const KEY_CATEGORIES = 'categories';
    const KEY_TAGS = 'tags';
    const KEY_REDIRECT = 'redirect';
    const KEY_META = 'meta';

    /**
     * @param array $data
     * @return Content
     * @throws ContentException
     */
    public function fromData(array $data)
    {
        $content = new Content();

        if (!array_key_exists(static::KEY_POST_DATE, $data)) {
            throw new ContentException('Unfortunately no post date is configured.');
        }

        if (!array_key_exists(static::KEY_LOCALE, $data)) {
            throw new ContentException('Unfortunately no locale is configured.');
        }

        if (!array_key_exists(static::KEY_LOCALE, $data)) {
            throw new ContentException('Unfortunately no status is configured.');
        }

        $content
            // ToDo: think about validation of the date and maybe accept different formats. -> Util
            ->setPostDate(\DateTime::createFromFormat('Y-m-d', $data[static::KEY_POST_DATE]))
            ->setLocale($data[static::KEY_LOCALE])
            ->setStatus($data[static::KEY_STATUS]);

        return $this->fromLocaleData($content);
    }

    private function fromLocaleData(Content $content): Content
    {
        $localeCollection = [];
        foreach ($content->getLocale() as $key => $value) {
            $locale = new Locale();

            if (!array_key_exists(static::KEY_SLUG, $value)) {
                throw new ContentException("Unfortunately no slug is configured for locale.");
            }

            if (!array_key_exists(static::KEY_TITLE, $value)) {
                throw new ContentException('Unfortunately no title is configured for locale.');
            }

            if (!array_key_exists(static::KEY_DESCRIPTION, $value)) {
                throw new ContentException('Unfortunately no description is configured for locale.');
            }

            if (array_key_exists(static::KEY_CATEGORIES, $value)) {
                $locale->setCategories($value[static::KEY_CATEGORIES]);
            }

            if (array_key_exists(static::KEY_TAGS, $value)) {
                $locale->setTags($value[static::KEY_TAGS]);
            }

            if (array_key_exists(static::KEY_REDIRECT, $value)) {
                $locale->setRedirect($value[static::KEY_REDIRECT]);
            }

            $locale
                ->setLanguage($key)
                ->setSlug($value[static::KEY_SLUG])
                ->setTitle($value[static::KEY_TITLE])
                ->setDescription($value[static::KEY_DESCRIPTION]);

            if (isset($value[static::KEY_META]) && is_array($value[self::KEY_META])) {
                // ToDo: Chain of Functions calling one another may not be that nice.
                //  Maybe Find a nicer solution -> Don't forget that each locale has its own meta.
                $locale->setMeta($this->fromMetaData($value[static::KEY_META]));
            }
            $localeCollection[$key] = $locale;

        }

        return $content->setLocale($localeCollection);
    }

    private function fromMetaData(array $data): Meta
    {
        $meta = new Meta();

        if (isset($data['keywords']) && is_string($data['keywords'])) {
            $meta->setKeywords($data['keywords']);
        }

        if (isset($data['author']) && is_string($data['author'])) {
            $meta->setAuthor($data['author']);
        }

        if (isset($data['publisher']) && is_string($data['publisher'])) {
            $meta->setPublisher($data['publisher']);
        }

        if (isset($data['robots']) && is_string($data['robots'])) {
            $meta->setRobots($data['robots']);
        }

        if (isset($data['custom']) && is_array($data['custom'])) {
            $meta->setCustom($data['custom']);
        }

        return $meta;
    }
}
