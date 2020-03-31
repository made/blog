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
 * Class Author
 *
 * @package Made\Blog\Engine\Model
 */
class Author
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $nameDisplay;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $picture;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Author
     */
    public function setName(string $name): Author
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameDisplay(): ?string
    {
        return $this->nameDisplay;
    }

    /**
     * @param string|null $nameDisplay
     * @return Author
     */
    public function setNameDisplay(?string $nameDisplay): Author
    {
        $this->nameDisplay = $nameDisplay;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     * @return Author
     */
    public function setLocation(?string $location): Author
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Author
     */
    public function setTitle(?string $title): Author
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * @param string|null $picture
     * @return Author
     */
    public function setPicture(?string $picture): Author
    {
        $this->picture = $picture;
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
     * @return Author
     */
    public function setDescription(?string $description): Author
    {
        $this->description = $description;
        return $this;
    }
}
