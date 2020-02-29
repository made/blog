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
 * Class PostConfigurationMetaCustom
 *
 * @package Made\Blog\Engine\Model\Configuration\Post
 */
class PostConfigurationMetaCustom
{
    /**
     * @var string
     */
    private $element;

    /**
     * @var array|string[]
     */
    private $attributes;

    /**
     * @return string
     */
    public function getElement(): string
    {
        return $this->element;
    }

    /**
     * @param string $element
     * @return PostConfigurationMetaCustom
     */
    public function setElement(string $element): PostConfigurationMetaCustom
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array|string[] $attributes
     * @return PostConfigurationMetaCustom
     */
    public function setAttributes(array $attributes): PostConfigurationMetaCustom
    {
        $this->attributes = $attributes;
        return $this;
    }
}
