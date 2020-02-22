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

use Made\Blog\Engine\Exception\PostConfigurationException;
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationLocale;
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationMeta;
use Made\Blog\Engine\Model\Configuration\Post\MetaCustomAttributeConfiguration;
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationMetaCustom;
use Made\Blog\Engine\Model\Configuration\Post\PostConfiguration;

/**
 * Class PostConfigurationMapper
 *
 * @TODO Refactor.
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationMapper
{
    /**
     * TODO: Add a check for from...() and to...().
     */
    public const ACCEPTED_STATUS = ['draft', 'review', 'publish'];


    const KEY_ID = 'id';
    const KEY_POST_DATE = 'post_date';
    const KEY_PATH = 'path';
    const KEY_SLUG = 'slug';
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
     * @return PostConfiguration
     * @throws PostConfigurationException
     */
    public function fromData(array $data)
    {
        $postConfiguration = new PostConfiguration();

        if (!array_key_exists(static::KEY_POST_DATE, $data)) {
            throw new PostConfigurationException('Unfortunately no post date is configured.');
        }

        if (!array_key_exists(static::KEY_LOCALE, $data)) {
            throw new PostConfigurationException('Unfortunately no locale is configured.');
        }

        if (!array_key_exists(static::KEY_STATUS, $data)) {
            throw new PostConfigurationException('Unfortunately no status is configured.');
        }

        $postConfiguration
            // ToDo: think about validation of the date and maybe accept different formats. -> Util
            ->setId(basename($data[static::KEY_PATH]))
            ->setPath($data[static::KEY_PATH])
            ->setDate(\DateTime::createFromFormat('Y-m-d', $data[static::KEY_POST_DATE]))
            ->setLocale($data[static::KEY_LOCALE])
            ->setStatus($data[static::KEY_STATUS]);

        return $this->fromLocaleData($postConfiguration);
    }

    private function fromLocaleData(PostConfiguration $postConfiguration): PostConfiguration
    {
        $localeCollection = [];
        foreach ($postConfiguration->getLocale() as $key => $value) {
            $locale = new PostConfigurationLocale();

            if (!array_key_exists(static::KEY_SLUG, $value)) {
                throw new PostConfigurationException("Unfortunately no slug is configured for locale.");
            }

            if (!array_key_exists(static::KEY_TITLE, $value)) {
                throw new PostConfigurationException('Unfortunately no title is configured for locale.');
            }

            if (!array_key_exists(static::KEY_DESCRIPTION, $value)) {
                throw new PostConfigurationException('Unfortunately no description is configured for locale.');
            }

            if (array_key_exists(static::KEY_CATEGORIES, $value)) {
                $locale->setCategory($value[static::KEY_CATEGORIES]);
            }

            if (array_key_exists(static::KEY_TAGS, $value)) {
                $locale->setTags($value[static::KEY_TAGS]);
            }

            if (array_key_exists(static::KEY_REDIRECT, $value)) {
                $locale->setRedirect($value[static::KEY_REDIRECT]);
            }

            $locale
                ->setLocale($key)
                ->setSlug($value[static::KEY_SLUG])
                ->setTitle($value[static::KEY_TITLE])
                ->setDescription($value[static::KEY_DESCRIPTION]);

            if (isset($value[static::KEY_META]) && is_array($value[self::KEY_META])) {
                $locale->setMeta($this->fromMetaData($value[static::KEY_META]));
            }
            $localeCollection[$key] = $locale;

        }

        return $postConfiguration->setLocale($localeCollection);
    }

    private function fromMetaData(array $data): PostConfigurationMeta
    {
        $meta = new PostConfigurationMeta();

        if (!empty($data['keywords']) && is_string($data['keywords'])) {
            $meta->setKeywords($data['keywords']);
        }

        if (!empty($data['author']) && is_string($data['author'])) {
            $meta->setAuthor($data['author']);
        }

        if (!empty($data['publisher']) && is_string($data['publisher'])) {
            $meta->setPublisher($data['publisher']);
        }

        if (!empty($data['robots']) && is_string($data['robots'])) {
            $meta->setRobots($data['robots']);
        }

        if (!empty($data['custom']) && is_array($data['custom'])) {
            $meta->setCustomMeta($this->fromCustomData($data['custom']));
        }

        return $meta;
    }

    /**
     * @param array $data
     * @return PostConfigurationMetaCustom[]
     */
    private function fromCustomData(array $data): array
    {
        $elementCollection = [];
        foreach ($data as $array) {
            $element = new PostConfigurationMetaCustom();
            $element->setElement('meta');

            // ToDo: Either we should define a list of accepted types.
            //  or add a boolean like "endingTag" because some elements need one :)
            if (!empty($array['type'])) {
                $element->setElement($array['type']);
                $elementCollection[] = $element;
                continue;
            }

            foreach ($array as $key => $value) {
                $attribute = new MetaCustomAttributeConfiguration();
                $attribute
                    ->setAttribute($key)
                    ->setValue($value);
                $element->addAttribute($attribute);
            }
            $elementCollection[] = $element;
        }

        return $elementCollection;
    }
}
