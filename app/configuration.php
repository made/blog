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

use Cache\Cache;
use Cache\Psr16\Cache as Psr16Cache;
use Made\Blog\Engine\Model\Configuration;
use Made\Blog\Engine\Repository\Implementation\File\PostConfigurationRepository as PostConfigurationRepositoryFile;
use Made\Blog\Theme\Basic\Controller\BlogController as BlogControllerBasic;
use Monolog\Logger;
use Slim\Views\Twig;

return [
    Logger::class => [
        'name' => 'app',
        'filename' => dirname(__DIR__) . '/var/log/app.log',
    ],

    Twig::class => [
        'debug' => true,
        'cache' => false,
    ],

    Configuration::class => [
        Configuration::CONFIGURATION_NAME_ROOT_DIRECTORY => dirname(__DIR__),
        Configuration::CONFIGURATION_NAME_FALLBACK_LOCALE => 'en',
    ],

    Cache::class => [
        'path' => dirname(__DIR__) . '/var/cache',
    ],

    Psr16Cache::class => [
        'cache_expiry_time' => strtotime('-1 Hour', 0),
    ],

    PostConfigurationRepositoryFile::class => [
        'default' => require dirname(__DIR__) . '/app/configuration.post.php',
    ],

    BlogControllerBasic::class => [
        'aboutText' => <<<HTML
Some small text about this blog. <em>I like trains.</em>
HTML,
        'aboutLink' => [
            [
                'icon' => 'fab fa-github-alt',
                'name' => 'GitHub',
                'link' => 'https://github.com/made',
            ],
            [
                'icon' => 'fab fa-twitter',
                'name' => 'Twitter',
                'link' => 'https://twitter.com/made_dot_dev',
            ],
            [
                'icon' => null,
                'name' => 'made.dev',
                'link' => 'https://made.dev/',
            ],
        ],
    ],
];
