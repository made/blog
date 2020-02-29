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

namespace Made\Blog\Engine\Model\Configuration\Post;

/**
 * Class PostConfiguration
 *
 * @package Made\Blog\Engine\Model\Configuration\Post
 */
class PostConfiguration
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array|PostConfigurationLocale[]
     */
    private $locale;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return PostConfiguration
     */
    public function setId(string $id): PostConfiguration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array|PostConfigurationLocale[]
     */
    public function getLocale(): array
    {
        return $this->locale;
    }

    /**
     * @param array|PostConfigurationLocale[] $locale
     * @return PostConfiguration
     */
    public function setLocale(array $locale): PostConfiguration
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param PostConfigurationLocale $locale
     * @return PostConfiguration
     */
    public function addLocale(PostConfigurationLocale $locale): PostConfiguration
    {
        $this->locale[$locale->getLocale()] = $locale;
        return $this;
    }
}
