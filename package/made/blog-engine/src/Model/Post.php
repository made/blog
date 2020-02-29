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
 * Class Post
 *
 * @package Made\Blog\Engine\Model
 */
class Post
{
    /**
     * @var PostConfigurationLocale
     */
    private $configuration;

    /**
     * @var PostContent
     */
    private $content;

    /**
     * @return PostConfigurationLocale
     */
    public function getConfiguration(): PostConfigurationLocale
    {
        return $this->configuration;
    }

    /**
     * @param PostConfigurationLocale $configuration
     * @return Post
     */
    public function setConfiguration(PostConfigurationLocale $configuration): Post
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return PostContent
     */
    public function getContent(): PostContent
    {
        return $this->content;
    }

    /**
     * @param PostContent $content
     * @return Post
     */
    public function setContent(PostContent $content): Post
    {
        $this->content = $content;
        return $this;
    }
}
