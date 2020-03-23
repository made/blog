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

// Definition of global defaults for Post. Note that actually using these defaults depends on the post driver and
// therefor should be defined for each one separately. That way the defaults and the way they are defined can differ
// between different drivers.
return [
    'locale' => [
        'en' => [
            'meta' => [
                'author' => 'John Doe',
                'custom' => [
                    'google-site-verification' => 'kladsjlkdasjlkdaskl',
                    'yandex' => 'kladsjlkdasjlkdaskl',
                    'bing' => 'kladsjlkdasjlkdaskl',
                ],
            ],
        ],
        'de' => [
            'meta' => [
                'author' => 'Max Mustermann',
                'custom' => [
                    'google-site-verification' => 'kladsjlkdasjlkdaskl',
                    'yandex' => 'kladsjlkdasjlkdaskl',
                    'bing' => 'kladsjlkdasjlkdaskl',
                ],
            ],
        ],
    ],
];
