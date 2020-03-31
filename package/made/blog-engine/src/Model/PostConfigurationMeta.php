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

/**
 * Class PostConfigurationMeta
 *
 * @package Made\Blog\Engine\Model
 */
class PostConfigurationMeta
{
    /**
     * @var Author|null
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
    private $customMetaList;

    /**
     * @return Author|null
     */
    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    /**
     * @param Author|null $author
     * @return PostConfigurationMeta
     */
    public function setAuthor(?Author $author): PostConfigurationMeta
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
    public function getCustomMetaList(): ?array
    {
        return $this->customMetaList;
    }

    /**
     * @param array|PostConfigurationMetaCustom[]|null $customMetaList
     * @return PostConfigurationMeta
     */
    public function setCustomMetaList(?array $customMetaList): PostConfigurationMeta
    {
        $this->customMetaList = $customMetaList;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCustomMetaList(): bool
    {
        return !empty($this->customMetaList);
    }
}
