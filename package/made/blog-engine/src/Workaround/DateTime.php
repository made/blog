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

namespace Made\Blog\Engine\Workaround;

use DateTime as DateTimeInternal;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Class DateTime
 *
 * INFO: This is a workaround needed to achieve full caching ability with the current object graph, since the utilized
 *  library (brick/varexporter) of the cache implementation (gameplayjdk/php-file-cache) does not yet support "internal"
 *  classes such as DateTime. To counter that, I added this "replacement" class to mask the underlying "internal" class.
 *
 * TODO: Find a better solution, maybe using the hotfix approach utilizing composer auto-loading to replace a class at
 *  loading time. Then the Brick\VarExporter\Internal\ObjectExporter\InternalClassExporter would have to be replaced.
 *  Either way, that is no long-term solution!
 *
 * @package Made\Blog\Engine\Workaround
 */
class DateTime extends DateTimeInternal
{
    /**
     * @inheritDoc
     */
    public static function __set_state($array)
    {
        /** @var DateTimeInternal $dateTime */
        $dateTime = parent::__set_state($array);

        return (new self())
            ->setTimestamp($dateTime->getTimestamp());
    }

    /**
     * @inheritDoc
     */
    public static function createFromImmutable(DateTimeImmutable $datetTimeImmutable)
    {
        /** @var DateTimeInternal $dateTime */
        $dateTime = parent::createFromImmutable($datetTimeImmutable);

        return (new self())
            ->setTimestamp($dateTime->getTimestamp());
    }

    /**
     * @inheritDoc
     */
    public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
    {
        /** @var DateTimeInternal $dateTime */
        $dateTime = parent::createFromFormat($format, $time, $timezone);

        return (new self())
            ->setTimestamp($dateTime->getTimestamp());
    }
}
