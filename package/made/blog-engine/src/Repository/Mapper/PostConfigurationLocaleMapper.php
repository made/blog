<?php
/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Made\Blog\Engine\Repository\Mapper;

use DateTime;
use Made\Blog\Engine\Exception\MapperException;
use Made\Blog\Engine\Model\Configuration\Post\PostConfigurationLocale;

/**
 * Class PostConfigurationLocaleMapper
 *
 * @package Made\Blog\Engine\Repository\Mapper
 */
class PostConfigurationLocaleMapper
{
    use NormalizeValueArrayTrait;

    const KEY_ID = 'id';
    const KEY_LOCALE = 'locale';
    const KEY_STATUS = 'status';
    const KEY_DATE = 'date';
    const KEY_SLUG = 'slug';
    const KEY_TITLE = 'title';
    const KEY_META = 'meta';
    const KEY_CATEGORIES = 'categories';
    const KEY_TAGS = 'tags';
    const KEY_SLUG_REDIRECTS = 'redirect_slugs';

    const STATUS_VALID = [
        'draft',
        'review',
        'published',
    ];

    const DTS_FORMAT = 'Y-m-d';

    /**
     * @var PostConfigurationMetaMapper
     */
    private $postConfigurationMetaMapper;

    /**
     * PostConfigurationLocaleMapper constructor.
     * @param PostConfigurationMetaMapper $postConfigurationMetaMapper
     */
    public function __construct(PostConfigurationMetaMapper $postConfigurationMetaMapper)
    {
        $this->postConfigurationMetaMapper = $postConfigurationMetaMapper;
    }

    /**
     * @param array $data
     * @return PostConfigurationLocale
     * @throws MapperException
     */
    public function fromData(array $data): PostConfigurationLocale
    {
        $postConfigurationLocale = new PostConfigurationLocale();

        // Required:
        if (isset($data[static::KEY_ID]) && is_string($data[static::KEY_ID])) {
            $postConfigurationLocale->setId($data[static::KEY_ID]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_ID);
        }

        // Required:
        if (isset($data[static::KEY_LOCALE]) && is_string($data[static::KEY_LOCALE])) {
            $postConfigurationLocale->setLocale($data[static::KEY_LOCALE]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_LOCALE);
        }

        // Required:
        if (isset($data[static::KEY_STATUS]) && is_string($data[static::KEY_STATUS])
            && null !== ($status = $this->validateStatus($data[static::KEY_STATUS]))) {
            $postConfigurationLocale->setStatus($status);
        } else {
            throw new MapperException('Missing or invalid key: ' . static::KEY_STATUS);
        }

        if (isset($data[static::KEY_DATE]) && is_string($data[static::KEY_DATE])
            && null !== ($dateTime = $this->createDateTime($data[static::KEY_DATE]))) {

            $postConfigurationLocale->setDate($dateTime);
        } else {
            throw new MapperException('Missing or invalid key: ' . static::KEY_DATE);
        }

        // Required:
        if (isset($data[static::KEY_SLUG]) && is_string($data[static::KEY_SLUG])) {
            $postConfigurationLocale->setSlug($data[static::KEY_SLUG]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_SLUG);
        }

        // Required:
        if (isset($data[static::KEY_TITLE]) && is_string($data[static::KEY_TITLE])) {
            $postConfigurationLocale->setTitle($data[static::KEY_TITLE]);
        } else {
            throw new MapperException('Missing key: ' . static::KEY_TITLE);
        }

        // Optional:
        if (!empty($data[static::KEY_META]) && is_array($data[static::KEY_META])) {
            $postConfigurationLocale->setMeta(
                $this->postConfigurationMetaMapper->fromData($data[static::KEY_META])
            );
        }

        // Optional:
        if (isset($data[static::KEY_CATEGORIES]) && is_array($data[static::KEY_CATEGORIES]) && !empty($categories = $this->normalizeValueArray($data[static::KEY_CATEGORIES], true))) {
            $postConfigurationLocale->setCategories($categories);
        }

        // Optional:
        if (isset($data[static::KEY_TAGS]) && is_array($data[static::KEY_TAGS]) && !empty($tags = $this->normalizeValueArray($data[static::KEY_TAGS], true))) {
            $postConfigurationLocale->setTags($tags);
        }

        // Optional:
        if (isset($data[static::KEY_SLUG_REDIRECTS]) && is_array($data[static::KEY_SLUG_REDIRECTS]) && !empty($redirects = $this->normalizeValueArray($data[static::KEY_SLUG_REDIRECTS], true))) {
            $postConfigurationLocale->setSlugRedirects($redirects);
        }

        return $postConfigurationLocale;
    }

    /**
     * @param array|array[] $dataArray
     * @return array|PostConfigurationLocale[]
     * @throws MapperException
     */
    public function fromDataArray(array $dataArray): array
    {
        $array = [];

        foreach ($dataArray as $data) {
            $array[] = $this->fromData($data);
        }

        return $array;
    }

    /**
     * @param PostConfigurationLocale $postConfigurationLocale
     * @return array
     */
    public function toData(PostConfigurationLocale $postConfigurationLocale): array
    {
        $data = [];

        $data[static::KEY_ID] = $postConfigurationLocale->getId();
        $data[static::KEY_LOCALE] = $postConfigurationLocale->getLocale();
        $data[static::KEY_STATUS] = $postConfigurationLocale->getStatus();
        $data[static::KEY_DATE] = $postConfigurationLocale->getDate()
            ->format(static::DTS_FORMAT);
        $data[static::KEY_SLUG] = $postConfigurationLocale->getSlug();
        $data[static::KEY_TITLE] = $postConfigurationLocale->getTitle();
        $data[static::KEY_META] = $this->postConfigurationMetaMapper->toData(
            $postConfigurationLocale->getMeta()
        );
        $data[static::KEY_CATEGORIES] = $postConfigurationLocale->getCategories();
        $data[static::KEY_TAGS] = $postConfigurationLocale->getTags();
        $data[static::KEY_SLUG_REDIRECTS] = $postConfigurationLocale->getSlugRedirects();

        return $data;
    }

    /**
     * @param array|PostConfigurationLocale[] $array
     * @return array|array[]
     */
    public function toDataArray(array $array): array
    {
        $dataArray = [];

        foreach ($array as $postConfigurationMetaCustom) {
            $dataArray[] = $this->toData($postConfigurationMetaCustom);
        }

        return $dataArray;
    }

    /**
     * @param string $status
     * @return string|null
     */
    private function validateStatus(string $status): ?string
    {
        $status = strtolower($status);

        if (!in_array($status, static::STATUS_VALID, true)) {
            return null;
        }

        return $status;
    }

    /**
     * @param string $string
     * @return DateTime|null
     */
    private function createDateTime(string $string): ?DateTime
    {
        return DateTime::createFromFormat(static::DTS_FORMAT, $string) ?: null;
    }
}
