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

// The php built-in web-server's version of rewrites...
if (PHP_SAPI == 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $path = ltrim($url['path'], '/');

    if (!empty($path)) {
        $file = dirname(__DIR__) . '/public/' . $path;

        if (file_exists($file)) {
            return false;
        }
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Package;
use Pimple\Container;
use Pimple\Package\PackageInterface;
use Pimple\Psr11\Container as Psr11Container;
use Slim\Factory\AppFactory;

$configuration = [];
$configuration[Package::SERVICE_NAME_CONFIGURATION] = require dirname(__DIR__) . '/app/configuration.php';

$container = new Container($configuration);
AppFactory::setContainer(new Psr11Container($container));

/**
 * Instantiate App.
 *
 * In order for the factory to work you need to ensure you have installed a supported PSR-7 implementation of your
 * choice e.g.: Slim PSR-7 and a supported ServerRequest creator (included with Slim PSR-7).
 */
$app = AppFactory::create();
// Add Routing Middleware.
$app->addRoutingMiddleware();

/*
 * Add Error Handling Middleware.
 *
 * @param bool $displayErrorDetails -> Should be set to false in production.
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler.
 * @param bool $logErrorDetails -> Display error details in error log which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

/** @var array|PackageInterface[] $packageList */
$packageList = require dirname(__DIR__) . '/app/package.php';
ksort($packageList);

// Register every package from the list.
foreach ($packageList as $package) {
    if (!$package instanceof PackageInterface) {
        continue;
    }

    $container->register($package);
}

// Initialize the client package.
foreach ($packageList as $package) {
    if (!$package instanceof Package) {
        continue;
    }

    $package->initialize();
}

$app->run();

return true;
