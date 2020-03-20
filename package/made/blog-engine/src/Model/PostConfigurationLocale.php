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

namespace Made\Blog\Engine\Model;

use DateTime;

/**
 * Class PostConfigurationLocale
 *
 * @package Made\Blog\Engine\Model
 */
class PostConfigurationLocale
{
    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_PUBLISHED = 'published';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $status;

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
     * @var PostConfigurationMeta|null
     */
    private $meta;

    /**
     * @var array|Category[]|null
     */
    private $categoryList;

    /**
     * @var array|Tag[]|null
     */
    private $tagList;

    /**
     * @var array|string[]|null
     */
    private $slugRedirectList;

    /**
     * @var string
     */
    private $template;

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
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return PostConfigurationLocale
     */
    public function setOrigin(string $origin): PostConfigurationLocale
    {
        $this->origin = $origin;
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
     * @return PostConfigurationMeta|null
     */
    public function getMeta(): ?PostConfigurationMeta
    {
        return $this->meta;
    }

    /**
     * @param PostConfigurationMeta|null $meta
     * @return PostConfigurationLocale
     */
    public function setMeta(?PostConfigurationMeta $meta): PostConfigurationLocale
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return array|Category[]|null
     */
    public function getCategoryList(): ?array
    {
        return $this->categoryList;
    }

    /**
     * @param array|Category[]|null $categoryList
     * @return PostConfigurationLocale
     */
    public function setCategoryList($categoryList): PostConfigurationLocale
    {
        $this->categoryList = $categoryList;
        return $this;
    }

    /**
     * @return array|Tag[]|null
     */
    public function getTagList(): ?array
    {
        return $this->tagList;
    }

    /**
     * @param array|Tag[]|null $tagList
     * @return PostConfigurationLocale
     */
    public function setTagList(?array $tagList): PostConfigurationLocale
    {
        $this->tagList = $tagList;
        return $this;
    }

    /**
     * @return array|string[]|null
     */
    public function getSlugRedirectList(): ?array
    {
        return $this->slugRedirectList;
    }

    /**
     * @param array|string[]|null $slugRedirectList
     * @return PostConfigurationLocale
     */
    public function setSlugRedirectList(?array $slugRedirectList): PostConfigurationLocale
    {
        $this->slugRedirectList = $slugRedirectList;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return PostConfigurationLocale
     */
    public function setTemplate(string $template): PostConfigurationLocale
    {
        $this->template = $template;
        return $this;
    }
}
