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
 * Class PostConfiguration
 *
 * @package Made\Blog\Engine\Model
 */
class PostConfiguration
{
    const LOCALE_KEY_CURRENT = 'current';

    /**
     * @var string
     */
    private $id;

    /**
     * @var array|PostConfigurationLocale[]
     */
    private $localeList;

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
    public function getLocaleList(): array
    {
        return $this->localeList;
    }

    /**
     * @param array|PostConfigurationLocale[] $localeList
     * @return PostConfiguration
     */
    public function setLocaleList(array $localeList): PostConfiguration
    {
        $this->localeList = $localeList;
        return $this;
    }

    /**
     * Set a locale. When a locale key is given, it is used. Bz default the locale list key is the locale string. If the
     * locale string already exists as locale key, the locale key `"current"` ({@link LOCALE_KEY_CURRENT}) is used.
     *
     * @param PostConfigurationLocale $locale
     * @param string|null $localeKey
     * @return PostConfiguration
     */
    public function setLocale(PostConfigurationLocale $locale, ?string $localeKey = null): PostConfiguration
    {
        if (null === $localeKey) {
            $localeKey = $locale->getLocale();

            if (isset($this->localeList[$localeKey])) {
                $localeKey = static::LOCALE_KEY_CURRENT;
            }
        }

        $this->localeList[$localeKey] = $locale;
        return $this;
    }

    /**
     * Get a locale. If no locale key is provided, the locale key `"current"` ({@link LOCALE_KEY_CURRENT}) is used.
     *
     * @param string|null $localeKey
     * @return PostConfigurationLocale|null
     */
    public function getLocale(?string $localeKey = null): ?PostConfigurationLocale
    {
        if (null === $localeKey) {
            $localeKey = static::LOCALE_KEY_CURRENT;
        }

        return $this->localeList[$localeKey] ?? null;
    }
}
