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

namespace Made\Blog\Engine\Repository\Criteria;

/**
 * Class CriteriaLocale
 *
 * @package Made\Blog\Engine\Repository\Criteria
 *
 * @method CriteriaLocale setOffset(int $offset)
 * @method CriteriaLocale setLimit(int $limit)
 * @method CriteriaLocale setOrder(?Order $order)
 * @method CriteriaLocale setFilter(?Filter $filter)
 */
class CriteriaLocale extends Criteria
{
    /**
     * @var string
     */
    private $locale;

    /**
     * CriteriaLocale constructor.
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
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
     * @return CriteriaLocale
     */
    public function setLocale(string $locale): CriteriaLocale
    {
        $this->locale = $locale;
        return $this;
    }
}
